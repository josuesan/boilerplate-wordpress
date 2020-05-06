<?php
// /  => \\/
$domain = str_replace('/', "\\/", $argv[1]);
$domain = json_encode($domain);
echo str_replace('"', '', $domain);