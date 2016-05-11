from aristarchus import *
#############################################################
# CONSTANTS
#############################################################

#############################################################
# INPUTS
#############################################################
typealignment=argv[1]
imagefiles=argv[2:]
nimages=len(imagefiles)

#############################################################
# READ PROPERTIES
#############################################################
times=[]
APs=[]
APSs=[]
Rs=[]
dRs=[]
rms=[]
rmercs=[]
rspots=[]
rsps=[]
rps=[]
images=[]

minres=1e100
for imagefile in imagefiles:

    print TAB*0,"Reading image:",imagefile

    #FILE PROPERTIES
    obsdir, image,fname,ext=fileProperties(imagefile)
    images+=[dict(img=image,name=fname,ext=ext)]

    jfile="%s/scratch/%s-crop.json"%(obsdir,fname)
    conf=json2dict(jfile)

    times+=[conf["time"]]
    Rs+=[conf["R"]]
    dRs+=[conf["dR"]]

    rmercs+=[conf["rmerc"]]
    rms+=[conf["rm"]]
    APs+=[conf["AP"]]
    rps+=[conf["rp"]]

    rspots+=[conf["rspot"]]
    rsps+=[conf["rsp"]]
    APSs+=[conf["APS"]]

    
    crop="%s/scratch/%s-crop.png"%(obsdir,fname)
    cw,ch=Image.open(crop).size
    print TAB*1,"Image resolution (w,h) : ",cw,ch

    res=cw*ch
    if res<minres:
        Rcommon=conf["R"]
        hcommon=ch
        wcommon=cw
        minres=res

print TAB*0,"Minimum resolution (w,h) : ",wcommon,hcommon

times=np.array(times)
Rs=np.array(Rs)
dRs=np.array(dRs)

rmercs=np.array(rmercs)
rms=np.array(rms)
APs=np.array(APs)
rps=np.array(rps)

APSs=np.array(APSs)
rspots=np.array(rspots)
rsps=np.array(rsps)

#############################################################
#NEW PROPERTIES
#############################################################
#Subscript s stand for sorted arrays

# Time from reference
ts_s=np.zeros_like(rms)

# Angle from reference
qs=np.zeros_like(rms)
qs_s=np.zeros_like(rms)
# Distance from reference
ds=np.zeros_like(rms)
ds_s=np.zeros_like(rms)

if typealignment=="auto":

    #############################################################
    #AUTOALIGNMENT
    #############################################################

    #==================================================
    #SORT IMAGES ACCORDING TO RADIUS
    #==================================================
    irm=rms.argsort()[::-1]
    images_s=[images[i] for i in irm]

    times_s=times[irm]
    for i in xrange(1,nimages):ts_s[i]=times_s[i]-times_s[0]

    Rs_s=Rs[irm]
    dRs_s=dRs[irm]

    rmercs_s=rmercs[irm]

    rms_s=rms[irm]
    APs_s=APs[irm]
    rps_s=rps[irm]

    rspots_s=rspots[irm]

    #==================================================
    #SEARCH FOR A SOLUTION
    #==================================================
    print TAB*0,"Searching for an automatic alignment solution..."
    params=dict(ts=ts_s,rs=rms_s,verbose=0)
    solution=minimize(tdSlopeMinimize,[45*DEG],args=(params,))
    print TAB*1,"Solution status:",solution["success"]

    ql=solution["x"]
    qs_s,ds_s,b,m,y0,r,logp,s=tdSlope(ql,params)

    print TAB*0,"Motion properties from automatic alignment:"
    print TAB*1,"Angular velocity (R/h) = ",np.abs(m)
    print TAB*1,"Fit r = ",r
    print TAB*1,"log(p) = ",logp
    print TAB*1,"Impact parameter, b = ",b

    print TAB*0,"Solution:"
    print TAB*1,"Times: ",times_s
    print TAB*1,"Angles: ",qs_s*RAD

    qs_s=qs_s+APs_s*DEG
    print TAB*1,"Position angles: ",APs_s
    print TAB*1,"Final angles: ",qs_s*RAD

    #STORE ANGLES AND DISTANCES IN UNSORTED ARRAYS
    for i in xrange(nimages):
        qs[irm[i]]=qs_s[i]
        ds[irm[i]]=ds_s[i]
    
    #==================================================
    #PLOT SOLUTION
    #==================================================
    it=ts_s.argsort()
    ts_t=ts_s[it]
    ds_t=ds_s[it]

    tms=np.linspace(ts_t.min(),ts_t.max(),100)
    dms=m*tms+y0

    fig=plt.figure()
    ax=fig.gca()
    ax.plot(ts_t,ds_t,"rs",ms=20,mec='none')
    ax.plot(tms,dms,"r-",label=r"Linear fit, $\dot\theta$ = %.4f $\theta_\odot$/hour"%(m))
    ax.grid()
    ax.legend(loc='best')
    ax.set_xlabel("Time from reference position (hours)")
    ax.set_ylabel(r"Distance from reference position (apparent solar radii, $\theta_\odot$)")
    fig.savefig("%s/scratch/alignment-result.png"%obsdir)

else:

    #############################################################
    #SPOT ALIGNMENT
    #############################################################
    print TAB*0,"Using the sunspot to align..."

    #==================================================
    #SORT IMAGES ACCORDING TO TIME
    #==================================================
    it=times.argsort()
    images_s=[images[i] for i in it]

    times_s=times[it]
    for i in xrange(1,nimages):ts_s[i]=times_s[i]-times_s[0]

    Rs_s=Rs[it]
    dRs_s=dRs[it]

    rmercs_s=rmercs[it]
    rms_s=rms[it]

    APs_s=APs[it]
    rps_s=rps[it]

    rspots_s=rspots[it]
    
    #==================================================
    #ROTATION ANGLE
    #==================================================
    qs_s=APSs[it]*DEG-APs[it]*DEG
    status="success"

    print TAB*1,"Solution status:",status

    print TAB*0,"Solution:"
    print TAB*1,"Times: ",times_s
    print TAB*1,"Angles: ",qs_s*RAD

#############################################################
#ROTATE IMAGES
#############################################################
Alignment=Image.new('RGBA',(wcommon,hcommon),"white")
Background=Image.new('RGBA',(wcommon,hcommon),"black")

j=0
for i in xrange(nimages):

    #IMAGE INFORMATION
    image=images_s[i]
    fname=image["name"]

    #OPEN CROP IMAGE
    crop="%s/scratch/%s-crop.png"%(obsdir,fname)
    Crop=Image.open(crop)
    cw,ch=Crop.size
    sx=(1.*wcommon)/cw;sy=(1.*hcommon)/ch
    
    #RESIZE IMAGE
    Resize=Crop.resize((wcommon,hcommon))
    
    #ADJUST POSITION OF MERCURY & SUN
    rmercs_s[0]*=sx;rmercs_s[1]*=sy
    rspots_s[0]*=sx;rspots_s[1]*=sy
    rcen=np.array([cw/2.,ch/2.])
    
    #ROTATE IMAGE
    Rotated=Resize.rotate(qs_s[i]*RAD)
    rotated="%s/%s-rotated.%s"%(obsdir,fname,ext)
    Rotated.save(rotated)

    #BUILD ALIGNED
