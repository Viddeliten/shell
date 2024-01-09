# shell
shell website including login and feedback system

## Running locally with docker

 - Create a file .env based on .env.example. ABS_PATH should be '/var/www/html'
 - Add your domain to your local hosts file
 - run:
```
docker compose build
docker compose up
```
 - visit the domain you set in .env file
 - For phpmyadmin: http://127.0.0.1:8082
 
### Files related to the Docker setup 
 .env.example 
 Dockerfile
 config/
 docker-compose.yml
 wordpress-entrypoint.sh

## How to use
1. Put all the files in your web root for the site you are creating.
2. Rename the sample files and directory and edit them

The custom_content directory is where you put your stuff

### creating a new page
#### Linking from menu
Open file custom_content/globals.php and find definition of CUSTOM_PAGES_ARRAY. Add an array for you new page. Slug is the value important for link target.
#### Making the link work
Open custom_content/functions/page.php and add conditions in function custom_page_display appropriately.

Links work the following way:

SiTE_URL/$_GET['p']/$_GET['s']/$_GET['id']/$_GET['param1']/$_GET['param2']/$_GET['param3']

You can also refer to .htaccess where you will find the following rule:

RewriteRule ^([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$	index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5&param3=$6			[QSA,L]

<i>How you use these members of GET really is up to you, but the reason I named them like that is because I usually have a page, subpage and an id. Like for instance https://shell.viddewebb.se/feedback/single/1 where page ($_GET['p']) is feedback, subpage ($_GET['s']) is single and id ($_GET['id']) is 1. It just looks a little bit clearer in the code than if I just named them 1,2,3 or something. :)</i>

### Mod rewrite on nginx ###
Add the following to your location / block in nginx config file for the site to get it to work the same as .htaccess does in apache!

# Translations with gettext
You can have as many languages on your website as you like. I only set up for swedish and english.

Config file contains a setting DEFAULT_LANGUAGE which defines what language is shown before user makes any changes
NOTE: language setting needs to reside in config file, globals can contain translations and must be included after language setup.

The instructions below assumes you are a bit familiar with editing in POEditor
## for swedish translation:
In root, outside of custom_content:

Generate pot-file that can be merged with po-file to generate mo-file (2 commands):
```bash
xgettext --from-code=UTF-8 -o texts-sv.pot *.php														*/
find . -iname "*.php" | xargs xgettext --from-code=UTF-8 -k_e -k_x -k__ -o custom_content/translations/default.pot
```
Download .po and .pot, update from POT file, upload .po

Then to merge from shell translations, do (1 command):
```bash
msgcat sample-translations/sv_SE/LC_MESSAGES/sv_SE.po custom_content/translations/sv_SE/LC_MESSAGES/sv_SE.po -o custom_content/translations/sv_SE/LC_MESSAGES/sv_SE.po --use-first
```

# Some mod rewrites for Shell
rewrite ^/([^/.]+)/?$ /index.php?p=$1 last;
rewrite ^/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2 last;
rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3 last;
rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4 last;
rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5 last;
rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5&param3=$6 last;


(to be continued)
