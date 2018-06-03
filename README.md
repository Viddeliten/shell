# shell
shell website including login and feedback system

## How to use
1. Put all the files in your web root for the site you are creating.
2. Rename the sample files and directory and edit them

The custom_content directory is where you put your stuff (to be continued)

### creating a new page
#### Linking from menu
Open file custom_content/globals.php and find definition of CUSTOM_PAGES_ARRAY. Add an array for you new page. Slug is the value important for link target.
#### Making the link work
Open custom_content/functions/page.php and add conditions in function custom_page_display appropriately.

Links work the following way:

SiTE_URL/$_GET['p']/$_GET['s']/$_GET['id']/$_GET['param1']/$_GET['param2']/$_GET['param3']

You can also refer to .htaccess where you will find the following rule:

RewriteRule ^([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/([^/.]+)/?$	index.php?p=$1&s=$2&id=$3&param1=$4&param2=$5&param3=$6			[QSA,L]