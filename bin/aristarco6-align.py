from aristarchus import *
#############################################################
#INPUTS
#############################################################
obsdir=argv[1]
images=argv[2]

#############################################################
#PARAMETERS
#############################################################

#TOLERANCE TO DETERMINE IF A BORDER CORRESPOND TO A CIRCLE
DRTOL=5E-2
NTHRES=3

#############################################################
#ANALYZE IMAGES
#############################################################
limages=images.split(",")
nimages=len(limages)
ipos=np.arange(nimages)

times=[]
APs=[]
rms=[]
images=[]
i=0
minres=1e100
for image in limages:

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #SPLIT NAME
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    parts=image.split(".")
    ext=parts[-1]
    fname="".join(parts[:-1])
    images+=[dict(img=image,name=fname,ext=ext)]
    
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #READ PHP FILE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    php="%s/%s.php"%(obsdir,fname)
    config=parsePhp(php)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #READ IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    Data=imread("%s/%s"%(obsdir,image))
    Mono=Data[:,:,0]
    w,h=Mono.shape
    X,Y=np.meshgrid(np.arange(h),np.arange(w))
    maxval=Mono.max()
    print "\nImage %d: '%s', resolution %d x %d..."%(i,image,w,h)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #GET BORDER OF THE SUN
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    xthresmin=0;dRR=1e100

    #DETERMINE THE OPTIMAL THRESHOLD
    for xthres in np.linspace(2.0,10.0,NTHRES):
        border=detectBorder(Mono,threshold=maxval/xthres)
        rs=border["rs"]
        xc,yc=border["centroid"]    
        Rs=np.sqrt((rs[:,0]-xc)**2+(rs[:,1]-yc)**2)
        Rmean=Rs.mean()
        Rstd=Rs.std()
        dRr=Rstd/(1.*Rmean)
        if dRr<dRR:
            xthresmin=xthres
            bordermin=border
            xcenter=xc
            ycenter=yc
            R=Rmean
            dR=Rstd
            dRR=dRr
        #print "Threshold = %d, Rmean,Rstd,dR = "%(maxval/xthres),Rmean,Rstd,dRr

    nborder=rs.shape[0]
    print "Optimal solution:"
    print "\t","Threshold = maxval / %.2f"%(xthresmin)
    print "\t","R = %.2f +/- %.2f (%.5f)"%(R,dR,dRR)
    print "\t","Center = (%d,%d)"%(xcenter,ycenter)

    """
    plt.figure(figsize=(8,8))
    plt.plot(rs[:,0],rs[:,1],'ro',ms=5,mec='none')
    """

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #IF SOLAR DISK IS NOT COMPLETE FIND CENTER
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    qcomplete=True
    if dRR>DRTOL:qcomplete=False
    if qcomplete:
        print "The Sun is complete"
    if not qcomplete:
        print "The Sun has been chopped"
        x1=rs[nborder/3,0]
        y1=rs[nborder/3,1]
        x2=rs[nborder/2,0]
        y2=rs[nborder/2,1]
        
        #Secant midpoint
        xs=(x1+x2)/2.;ys=(y1+y2)/2.

        #Direction
        rl=np.cross([0,0,1],[x2-x1,y2-y1,0])
        rl=rl/norm(rl)
        rl=[rl[0],rl[1]]
        args=dict(rs=rs,rl=rl,rm=[xs,ys])

        dR=1E100
        for l in np.linspace(-w,w,100):
            Rr,dRr=radiusPoints(l,args)
            if dRr<dR:
                dR=dRr
                R=Rr
                lmin=l

        print "Radial dispersion at solution = ",dR
        dRR=dR/(1.*R)
        xcenter=xs+lmin*rl[0]
        ycenter=ys+lmin*rl[1]

        """
        plt.plot([x1],[y1],'bs',ms=10)
        plt.plot([x2],[y2],'rs',ms=10)
        plt.plot([xs],[ys],'gs',ms=10)
        plt.plot([x1,x2],[y1,y2],'k-')
        plt.axhline(ycenter)
        plt.axvline(xcenter)
        #"""

        print "After recalculation:"
        print "\t","R = %.2f +/- %.2f (%.5f)"%(R,dR,dRR)
        print "\t","Center = (%d,%d)"%(xcenter,ycenter)

    """
    plt.axhline(yc,color='r')
    plt.axvline(xc,color='r')
    ext=max(w,h)
    plt.xlim((0,ext))
    plt.ylim((0,ext))
    plt.savefig("tmp/c.png")
    break
    #"""

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #CROP IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    dp=0
    xmin=int(np.round(xcenter-R))-dp;xmax=int(np.round(xcenter+R))+dp
    ymin=int(np.round(ycenter-R))-dp;ymax=int(np.round(ycenter+R))+dp

    xmin=cropCoord(xmin,w)
    ymin=cropCoord(ymin,h)
    xmax=cropCoord(xmax,w)
    ymax=cropCoord(ymax,h)
    cw=xmax-xmin
    ch=ymax-ymin

    #Cropped image
    Crop=Data[ymin:ymax,xmin:xmax,:]
    images[i]["crop"]="%s/%s-crop-result.%s"%(obsdir,fname,ext)
    plt.imsave(images[i]["crop"],Crop)
    xcropcenter=roundFloat(xcenter-xmin)
    ycropcenter=roundFloat(ycenter-ymin)

    res=cw*ch
    if res<minres:
        hcommon=ch
        wcommon=cw
        minres=res

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #FIND MERCURY POSITION RESPECT TO CENTER
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    ptime=config["time"].split(":")
    time=float(ptime[0])+float(ptime[1])/60.+float(ptime[2])/3600.

    #DISTANCE TO CENTER
    ppos=config["posmercury"].split(",")
    xm=float(ppos[0]);ym=float(ppos[1])
    rm=np.sqrt((xm-xcenter)**2+(ym-ycenter)**2)/R

    #APPARENT POSITION ANGLE
    """
    AP : Angle between the rightmost point of the Sun and the line towards
    Mercury meaured in the clockwise direction.
    """
    AP=np.arctan2((ym-ycenter),(xm-xcenter))*RAD
    print "Mercury position : r = %.5f, AP = %.2f deg"%(rm,AP)

    times+=[time]
    rms+=[rm]
    APs+=[AP]

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #ROTATE IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    Rotated=ndimage.rotate(Crop,AP,reshape=False)
    cond=Rotated[:,:,3]!=255
    Rotated[cond,3]=255
    rotated="%s/%s-rotated-result.%s"%(obsdir,fname,ext)
    plt.imsave(rotated,Rotated)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #SAVE PHP CONFIGURATION FILE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    fp=open("%s/%s-align.php"%(obsdir,fname),"w")
    fp.write("""<?php
$center='%d,%d';
$cropceneter='%d,%d';
$R='%.2f';
$dR='%.2f';
$tm='%.8f';
$rm='%.5f';
$AP='%.2f';
?>"""%(xcenter,ycenter,xcropcenter,ycropcenter,R,dR,time,rm,AP))
    fp.close()
    i+=1

times=np.array(times)
rms=np.array(rms)
APs=np.array(APs)

#############################################################
#SORT IMAGES ACCORDING TO RADIUS
#############################################################
irm=rms.argsort()[::-1]
rms_s=rms[irm]
times_s=times[irm]
APs_s=APs[irm]
images=[images[i] for i in irm]

print "\nPoint order:",irm

rs=rms[irm]
print "Radii:",rs

ts=np.zeros(nimages)
for i in xrange(1,nimages):ts[i]=times_s[i]-times_s[0]
print "Times:",ts

#############################################################
#SEARCH FOR A SOLUTION
#############################################################
params=dict(ts=ts,rs=rs,verbose=0)
solution=minimize(tdSlopeMinimize,[45*DEG],args=(params,))
ql=solution["x"]
qs,ds,B,m,b,r,logp,s=tdSlope(ql,params)
print "Angles: ",qs*RAD

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#PLOT SOLUTION
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
it=ts.argsort()
ts_s=ts[it]
ds_s=ds[it]
tms=np.linspace(0,ts_s[-1],100)
dms=m*tms+b
fig=plt.figure()
ax=fig.gca()
ax.plot(ts,ds,"k+",ms=20)
ax.plot(tms,dms,"r-",label=r"Linear fit, $\dot\theta$ = %.4f $\theta_\odot$/hour"%(m))

ax.grid()
ax.legend(loc='best')
ax.set_xlabel("Time from most external position (hours)")
ax.set_ylabel(r"Distance between points (apparent solar radii, $\theta_\odot$)")
fig.savefig("%s/alignment-result.png"%obsdir)

#############################################################
#ROTATE IMAGES
#############################################################
j=0
for i in irm:
    
    # Get image information
    image=images[i]
    print "File: ",image["name"]

    rotated="%s/%s-rotated-result.%s"%(obsdir,image["name"],image["ext"])
    Rotated=imread(rotated)
    final="%s/%s-final-result.%s"%(obsdir,image["name"],image["ext"])

    # Adjusting to a common size
    print "\t","Ajusting image to common resolution %sx%s"%(hcommon,wcommon)
    Rotated=imresize(Rotated,(hcommon,wcommon))
    plt.imsave(rotated,Rotated)

    if j==0:
        Alignment=Rotated
        system("cp %s %s"%(rotated,final))
        j+=1
        continue

    print "\t","Rotating to final position image %d, r = %.2f, q = %.2f..."%(i,rs[i],qs[i]*RAD)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #LOAD ROTATED IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    Final=ndimage.rotate(Rotated,qs[i]*RAD,reshape=False)
    cond=Final[:,:,3]!=255
    Final[cond,3]=255
    plt.imsave(final,Final)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #CALCULATE RESULTING IMAGE5
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    Alignment=np.minimum(Alignment,Final)
    j+=1

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#FINAL ROTATION
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
Alignment=ndimage.rotate(Alignment,qs[0]*RAD,reshape=False)
cond=Alignment[:,:,3]!=255
Alignment[cond,3]=255
alignment="%s/image-alignment-result.%s"%(obsdir,image["ext"])
plt.imsave(alignment,Alignment)

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#HTML
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
html="""
<img src='%s/image-alignment-result.png'/>
"""%(obsdir
print html
