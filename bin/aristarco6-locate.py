from aristarchus import *
#############################################################
#INPUTS
#############################################################
img=argv[1]
coords=argv[2]

#############################################################
#READ IMAGE
#############################################################
data=imread(img)
image=data[:,:,1]
w,h=image.shape

#############################################################
#GET SUBIMAGE
#############################################################
coords=eval("np.array(["+coords+"])")
coords[0::2]*=w
coords[1::2]*=h
subimage=image[coords[1]:coords[3],coords[0]:coords[2]]
sw,sh=subimage.shape

#############################################################
#FIND SPOT PIXELS
#############################################################
maxval=subimage.max()
js=np.arange(sh)
xs=[];ys=[]
for i in xrange(sw):
    line=subimage[i,:]
    cond=line<0.7*maxval
    limits=js[cond]
    if len(limits)>0:
        ys+=[i]*len(limits)
        xs+=limits.tolist()

if len(xs)==0 or len(ys)==0:
    print "No object found"
    exit(1)

#############################################################
#CENTROID
#############################################################
xmean=np.mean(xs)
ymean=np.mean(ys)
xmerc=xmean+coords[0]
ymerc=ymean+coords[1]

#############################################################
#OUTPUT
#############################################################
print "%d,%d"%(int(round(xmerc)),int(round(ymerc)))
