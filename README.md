# MDS: Museum Data Services

A micro-site to merge and view the data from the MDS API

It's a bizarre API, you get a key that start off the record fetch ("extract") and in that result is the link to the next set.


curl "https://museumdata.uk/explore-collections/?_sfm_has_object_records=1&_sf_ppp=100"  > data/museums.html 
