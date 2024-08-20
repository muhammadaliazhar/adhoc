#!/usr/bin/env python
# Author: Peter Czupil

import googlemaps
import argparse
from datetime import datetime

gmaps = googlemaps.Client(key='AIzaSyA1mgVkzaBv_Fxvcu5PDJhjcMt2DJEQbKs')

parser = argparse.ArgumentParser(description='Get lat and lng from address.')
parser.add_argument('Address', metavar='address', type=str, help='Address string to find geocoordinates for')
args = parser.parse_args()

geocode_result = gmaps.geocode(args.Address)
print str(geocode_result[0]['geometry']['location']['lat']) + '|' + str(geocode_result[0]['geometry']['location']['lng'])
