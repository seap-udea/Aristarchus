#!/usr/bin/env python
#############################################################
# EXTRENAL LIBRARIES
#############################################################
from matplotlib import use
use('Agg')
import matplotlib.pylab as plt
import matplotlib.cm as cm
import numpy as np
from sys import argv,exit
from os import system
from scipy.misc import *
from scipy.optimize import minimize
import re
import itertools as it

#############################################################
# MACROS
#############################################################
norm=np.linalg.norm

#############################################################
# CONSTANTS
#############################################################
DEG=np.pi/180
RAD=180/np.pi

#############################################################
# ROUTINES
#############################################################
def parsePhp(file):
    f=open(file,"r")
    config=dict()
    for line in f:
        line=line.strip()
        if '<' in line or\
           '>' in line:
            continue
        parts=line.split("=");
        var=parts[0][1:]
        value=parts[1]
        value=value.replace("\"","");
        value=value.replace("\'","");
        value=value.replace(";","");
        config[var]=value
    return config

def detectBorder(imagen,threshold=50,ptol=3):

    #Get image properties
    ch,cw=imagen.shape

    xs=np.arange(cw)
    ys=np.arange(ch)
    rs=[]

    #Get points in horizontal sections
    period=1
    for i in xrange(ch):
        if (i%period):continue
        cond=imagen[i,:]>=threshold
        row=xs[cond]
        if len(row)==0:continue
        xmin=row[0]
        xmax=row[-1]
        if (xmin>ptol):rs+=[[xmin,i]]
        if (cw-xmax)>ptol:rs+=[[xmax,i]]
    #Get points in vertical sections
    for j in xrange(cw):
        if (j%period):continue
        cond=(imagen[:,j]>=threshold)
        col=ys[cond]
        if len(col)==0:continue
        ymin=col[0]
        ymax=col[-1]
        if (ymin>ptol):rs+=[[j,ymin]]
        if (ch-ymax)>ptol:rs+=[[j,ymax]]
    rs=np.array(rs)

    #Sort according to abcisa
    rslist=rs.tolist()
    rslist.sort(key=lambda row:row[1])
    rs=np.array(rslist)
    #Remove unique
    rs=np.vstack({tuple(row) for row in rs})

    #Compute the centroid
    xc=rs[:,0].mean()
    yc=rs[:,1].mean()

    #Sort according to angle with respect to centroid
    rslist=rs.tolist()
    rslist.sort(key=lambda row:np.arctan2((row[1]-yc),(row[0]-xc)))
    rs=np.array(rslist)
    
    #Return dict
    border=dict(rs=rs,centroid=[xc,yc])

    return border

def radiusPoints(l,args):
    rs=args["rs"]
    rl=args["rl"]
    rm=args["rm"]

    xc=rm[0]+l*rl[0]
    yc=rm[1]+l*rl[1]

    Rs=np.sqrt((rs[:,0]-xc)**2+(rs[:,1]-yc)**2)
    Rsmean=Rs.mean()
    dRs=Rs.std()
    return Rsmean,dRs

def quadraticEquation(a,b,c):
    disc=b**2-4*a*c
    if disc<0:
        print "Complex roots"
        return 0,0
    else:
        disc=np.sqrt(disc)
        x1=(-b+disc)/(2*a)
        x2=(-b-disc)/(2*a)
        return x1,x2
