# shell
shell website including login and feedback system

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

                # Some mod rewrites for Shell
               rewrite ^/([^/.]+)/?$ /index.php?p=$1 last;
               rewrite ^/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2 last;
               rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3 last;
               rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4 last;
               rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5 last;
               rewrite ^/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$ /index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5&param3=$6 last;



(to be continued)
