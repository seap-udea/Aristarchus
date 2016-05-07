    #Border of the "white" area
    contour=plt.contour(cond,levels=[0.5])
    border=[]
    R=w/2
    for path in contour.collections[0].get_paths():
        for points in path.vertices:
            print points
            if abs(points[0]-xm)>R or abs(points[1]-ym)>R:
                continue
            border+=[points.tolist()]
    border=np.array(border)
    
    plt.figure()
    plt.plot(border[:,0],border[:,1])
    plt.savefig("tmp/c.png")

    #Border of the "white" area
    contour=plt.contour(cond,levels=[0.5])
    border=[]
    R=w/2
    for path in contour.collections[0].get_paths():
        for points in path.vertices:
            if abs(points[0]-xm)>R or abs(points[1]-ym)>R:
                continue
            border+=[points.tolist()]
    border=np.array(border)
    plt.figure(figsize=(6,6))
    plt.plot(border[:,0],border[:,1],'ko',ms=0.1)
    ext=max(w,h)
    plt.xlim((0,ext))
    plt.ylim((0,ext))
    plt.savefig("tmp/c.png")
    break


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
    print xmin,xmax,ymin,ymax
    exit(0)
    

nex=0
meanex=0
for i in xrange(sh):
    line=subimage[i,:]
    condin=line<(meanval-2*stdval)
    condex=line>=(meanval-2*stdval)

    limits=js[condin]
    if len(limits)>0:
        ys+=[i]*len(limits)
        xs+=limits.tolist()

    exterior=js[condex]
    if len(exterior):
        meanex+=subimage[i,exterior].sum()
        nex+=len(exterior)
meanex/=(1.0*nex)

147,242,0,0 [[139, 138, 146], [138, 136, 153], [131, 76, 107], [102, 72], [118, 104, 121], [130, 137, 139], [139, 138, 146], [138, 136, 153], [131, 76, 107], [102, 72], [118, 104, 121], [130, 137, 139], [139, 138, 146], [138, 136, 153], [131, 76, 107], [102, 72], [118, 104, 121], [130, 137, 139]] [54.454260679724818, 72, 72] 72 153 85.8937970065 107.1 117.333333333 [ 147.055  147.055  147.055] [ 242.1538  242.1538  242.1538]
