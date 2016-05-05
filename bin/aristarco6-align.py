from aristarchus import *
#############################################################
#INPUTS
#############################################################
obsdir=argv[1]
images=argv[2]

#############################################################
#ANALYZE IMAGES
#############################################################
limages=images.split(",")
nimages=len(limages)

for image in limages:

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #SPLIT NAME
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    parts=image.split(".")
    ext=parts[-1]
    fname="".join(parts[:-1])

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #READ PHP FILE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    php="%s/%s.php"%(obsdir,fname)
    config=parsePhp(php)

    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    #CROP IMAGE
    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    data=imread("%s/%s"%(obsdir,image))[:,:,0]
    w,h=data.shape
    
    

    break
