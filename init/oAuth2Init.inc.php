<?php

$client = new Google_Client ();
$client->setClientId ( $config['OAUTH2_CLIENT_ID'] );
$client->setClientSecret ( $config['OAUTH2_CLIENT_SECRET'] );
$client->setScopes ( 'https://www.googleapis.com/auth/youtube' );
// check that this url is set as redirect-url in your project on developers.code.google.com
$redirect = filter_var ( 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['PHP_SELF'], FILTER_SANITIZE_URL );

$client->setRedirectUri ($redirect);

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube ( $client );

if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['token'] = $client->getAccessToken();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset ( $_SESSION ['token'] )) {
	$client->setAccessToken ( $_SESSION ['token'] );
}

if(!$client->getAccessToken()) {
	// If the user hasn't authorized the app, initiate the OAuth flow
	$state = mt_rand ();
	$client->setState ( $state );
	$_SESSION ['state'] = $state;
	
	$authUrl = $client->createAuthUrl ();
	$htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}