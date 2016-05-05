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
import re

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
