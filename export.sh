#!/bin/sh
# Count number of existing migrations
filecount=$(ls -l database/ | egrep -c '^-')

BASE_URL=$(grep SITE_URL .env | xargs)
BASE_URL=${BASE_URL#*=}
# Increment one to the new migration and create the file
protocol=$(php protocol.php $BASE_URL)
domain=$(php domain.php $BASE_URL)
escape=$(php escape.php $BASE_URL)
encode=$(php encode.php "$protocol://$domain")
wp --info
echo "DOMAIN = $domain"
echo "PROTOCOL = $protocol"
echo "ESCAPE = $escape"
filecount=$(($filecount + 1 ))
wp search-replace $protocol://$domain {BASE_URL} --allow-root --precise --all-tables
wp search-replace $domain {DOMAIN} --allow-root --precise --all-tables
wp search-replace $encode {ENCODE} --allow-root --precise --all-tables
wp search-replace $escape {ESCAPE} --allow-root --precise --all-tables
wp db export database/$filecount-migration.sql
wp search-replace {BASE_URL} $protocol://$domain --allow-root --precise --all-tables
wp search-replace {DOMAIN} $domain --allow-root --precise --all-tables
wp search-replace {ENCODE} $encode --allow-root --precise --all-tables
wp search-replace {ESCAPE} $escape --allow-root --precise --all-tables