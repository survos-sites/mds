# MDS: Museum Data Services

A micro-site to merge and view the data from the MDS API

It's a bizarre API, you get a key that start off the record fetch ("extract") and in that result is the link to the next set.


curl "https://museumdata.uk/explore-collections/?_sfm_has_object_records=1&_sf_ppp=100"  > data/museums.html 


bin/console doctrine:query:sql "delete from messenger_messages where queue_name='failed'" 

## 

## Entities

Grp: high-level, the Museum.  To extract the objects, we need a "page" that is the object listing.  Was 10/page, now is 100

add to app.json

```json
    "cron": [
        {
            "command": "php -d memory_limit=4G bin/console messenger:consume extract_fetch  --time-limit 53",
            "schedule": "*/1 * * * *"
        },
        {
            "command": "php -d memory_limit=4G bin/console messenger:consume grp_extract extract_fetch extract_load  --time-limit 175",
            "schedule": "*/3 * * * *"
        },
        {
            "command": "php -d memory_limit=4G bin/console messenger:consume extract_fetch extract_load  --time-limit 175",
            "schedule": "*/3 * * * *"
        },
        {
            "command": "php -d memory_limit=4G bin/console messenger:consume extract_load  --time-limit 230",
            "schedule": "*/4 * * * *"
        }
    ],


```
