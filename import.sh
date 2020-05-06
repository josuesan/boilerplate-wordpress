#!/bin/sh

# Count number of existing migrations
filecount=$(ls -l database/ | egrep -c '^-')

BASE_URL=$(grep SITE_URL .env | xargs)
BASE_URL=${BASE_URL#*=}
# Execute import command and replace token '{BASE_URL}'
protocol=$(php protocol.php $BASE_URL)
domain=$(php domain.php $BASE_URL)
escape=$(php escape.php $BASE_URL)
encode=$(php encode.php "$protocol://$domain")
wp --info
echo "DOMAIN = $domain"
echo "PROTOCOL = $protocol"
echo "ESCAPE = $escape"
wp db import database/$filecount-migration.sql --allow-root 
wp search-replace {BASE_URL} $protocol://$domain --allow-root --precise --all-tables
wp search-replace {DOMAIN} $domain --allow-root --precise --all-tables
wp search-replace {ENCODE} $encode --allow-root --precise --all-tables
wp search-replace {ESCAPE} $escape --allow-root --precise --all-tables