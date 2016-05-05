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
    print "Image '%s', resolution %d x %d..."%(image,w,h)

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

    plt.figure(figsize=(8,8))
    plt.plot(rs[:,0],rs[:,1],'ro',ms=5,mec='none')

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
    #CROP IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #"White" area
    cond=Mono>=maxval/xthresmin
    xs=X[cond]
    ys=Y[cond]
    
    #Mean-Min-Max
    xm=xs.mean();ym=ys.mean()
    xmin=xs.min();xmax=xs.max()
    ymin=ys.min();ymax=ys.max()

    #Cropped image
    Crop=Data[ymin:ymax,xmin:xmax,:]
    plt.imsave("%s/%s-crop.%s"%(obsdir,fname,ext),Crop)
    cMono=Crop[:,:,0]
    ch,cw=cMono.shape

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #SAVE PHP CONFIGURATION FILE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    fp=open("%s/%s-align.php"%(obsdir,fname),"w")
    fp.write("""<?php
$center='%d,%d';
$R='%.2f';
$dR='%.2f';
$tm='%.8f';
$rm='%.5f';
$AP='%.2f';
?>"""%(xcenter,ycenter,R,dR,time,rm,AP))
    fp.close()

times=np.array(times)
rms=np.array(rms)
APs=np.array(APs)

#############################################################
#CHOOSE THE MOST CONVENIENT CONBINATION
#############################################################
trios=it.combinations(ipos,3)
dtmax=0
for trio in trios:
    timestrio=times[list(trio)]
    dtimes=timestrio[1:]-timestrio[:-1]
    dtmean=dtimes.mean()
    if dtmean>=dtmax:
        dtmax=dtmean
        triomax=list(trio)

print "Better trio for analysis (dtmean = %.3f): "%(dtmax),triomax
triomax=list(trio)
rms_sol=rms[triomax]
times_sol=times[triomax]
APs_sol=APs[triomax]
images_sol=[images[i] for i in triomax]

#############################################################
#SORT IMAGES ACCORDING TO RADIUS
#############################################################
irm_s=rms_sol.argsort()[::-1]

print "Order by radii = ",irm_s
rms_s=rms_sol[irm_s]
times_s=times_sol[irm_s]
APs_s=APs_sol[irm_s]
images_s=[images_sol[i] for i in irm_s]

#############################################################
#SEARCH SOLUTION
#############################################################
r0=rms_s[0]
r1=rms_s[1]
r2=rms_s[2]

i=0
for q2 in np.linspace(1*DEG,180*DEG,50):

    print "Testing q2 = %.4f"%(q2*RAD)

    # CALCULATE CONDITIONS FOR POSITION 2
    t2=times_s[2]-times_s[0]
    d2=np.sqrt(r0**2+r2**2-2*r0*r2*np.cos(q2))
    q0=np.arcsin(np.sin(q2)*r1/d2)
    print "t2 = %.5f, d2 = %.6f, q0 = %.4f, q2 = %.4f"%(t2,d2,q0*RAD,q2*RAD)

    # CALCULATE CONDITION FOR POSITION 1
    if q0==0:
        t1=times_s[1]-times_s[0]
        if t1<t2:
            d1=r0-r1
            q1=0.0
        else:
            d1=r0+r1
            q1=np.pi
    else:
        t1=times_s[1]-times_s[0]
        a=r1**2/np.sin(q0)**2
        b=-2*r0*r1
        c=(r0**2+r1**2-a)
        cq1_1,cq1_2=quadraticEquation(a,b,c)
        if cq1_1!=0 and cq1_2!=0:
            q1_1=np.arccos(cq1_1)
            q1_2=np.arccos(cq1_2)
            if t1>t2:
                q1=max(q1_1,q1_2,q2)
            else:
                q1=min(q1_1,q1_2,q2)
            d1=np.sqrt(r0**2+r1**2-2*r0*r1*np.cos(q1))
        else:
            print "No solution."
        print "t1 = %.5f, d1 = %.6f, q1 = %.4f"%(t1,d1,q1*RAD)

    # COMPARE DISTANCES WITH TIMES
    print "(d2/d1) = %.4f = (t2/t1) = %.4f? => f = %.4f "%(d2/d1,t2/t1,(d2/d1)-(t2/t1))

    i+=1
    #if i>10:break

