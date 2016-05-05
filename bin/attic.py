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
