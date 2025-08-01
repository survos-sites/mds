# MDS: Museum Data Services

A micro-site to merge and view the data from the MDS API.

It's a bizarre API, you get a key that start off the record fetch ("extract") and in that result is the link to the next set.

## Messenger Configuration

**Important**: MDS uses a single exchange messenger architecture with dynamic routing middleware. Make sure to use the correct DSN:

```bash
# ✅ Correct - Use phpamqplib:// for Dynamic Routing Middleware support
MESSENGER_TRANSPORT_DSN_RABBITMQ=phpamqplib://guest:guest@localhost:5672/mds

# ❌ Incorrect - Don't use amqp:// (limited routing capabilities)
# MESSENGER_TRANSPORT_DSN_RABBITMQ=amqp://guest:guest@localhost:5672/mds
```

See [docs/](docs/) for detailed messenger configuration documentation.

## Workflow

Create the database and migration (via migrations or d:sc:update --force if sqlite)

```bash
git clone git@github.com:survos-sites/mds && cd mds
composer install

bin/console d:d:drop --force --if-exists
bin/console d:d:create 
bin/console d:m:m -n

# OR

echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
echo "MEILI_SERVER=http://127.0.0.1:7700" >> .env.local
bin/console d:sc:update --force


# create the meili indexes
bin/console meili:index --reset
symfony server:start -d

# load the Grp records, marking=new
bin/console load:Grp --max 3 
# dispatch a request fetch the API keys
bin/console iterate Grp -m new -t get_api_key 

# the workflow should now start
bin/consume


```


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
