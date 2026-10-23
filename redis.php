<?php

// composer require predis/predis
require 'vendor/autoload.php';

$client = new Predis\Client('rediss://default:gQAAAAAAA5snAAIgcDIxNTI1OTBhZmZkMjY0OGVmYTg4N2Y0ZDVmZTY4MzM1Yw@immune-parakeet-236327.upstash.io:6379');

$client->set("foo", "bar");

print_r($client->get("foo"));