<?php

$client = new Google_Client ();
$client->setDeveloperKey ( $config ['DEVELOPER_KEY'] );

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube ( $client );