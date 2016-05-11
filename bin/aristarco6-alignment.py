from aristarchus import *
#############################################################
# CONSTANTS
#############################################################

#############################################################
# INPUTS
#############################################################
typealignment=argv[1]
imagefiles=argv[2:]

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

for imagefile in imagefiles:

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


print rmercs

qs=np.zeros_like(rms)
ds=np.zeros_like(rms)
    
