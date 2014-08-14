<?php

// Call set_include_path() as needed to point to your client library.
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';
session_start ();

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}> For more information about using OAuth 2.0 to access Google APIs, please see: <https://developers.google.com/youtube/v3/guides/authentication> Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = 'REPLACE_ME';
$OAUTH2_CLIENT_SECRET = 'REPLACE_ME';

$client = new Google_Client ();
$client->setClientId ( $OAUTH2_CLIENT_ID );
$client->setClientSecret ( $OAUTH2_CLIENT_SECRET );
$client->setScopes ( 'https://www.googleapis.com/auth/youtube' );
$redirect = filter_var ( 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['PHP_SELF'], FILTER_SANITIZE_URL );
$client->setRedirectUri ( $redirect );

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube ( $client );

if (isset ( $_GET ['code'] )) {
	if (strval ( $_SESSION ['state'] ) !== strval ( $_GET ['state'] )) {
		die ( 'The session state did not match.' );
	}
	
	$client->authenticate ( $_GET ['code'] );
	$_SESSION ['token'] = $client->getAccessToken ();
	header ( 'Location: ' . $redirect );
}

if (isset ( $_SESSION ['token'] )) {
	$client->setAccessToken ( $_SESSION ['token'] );
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken ()) {
	try {
		// This code subscribes the authenticated user to the specified channel.
		
		// Identify the resource being subscribed to by specifying its channel ID
		// and kind.
		$resourceId = new Google_Service_YouTube_ResourceId ();
		$resourceId->setChannelId ( 'UCtVd0c0tGXuTSbU5d8cSBUg' );
		$resourceId->setKind ( 'youtube#channel' );
		
		// Create a snippet object and set its resource ID.
		$subscriptionSnippet = new Google_Service_YouTube_SubscriptionSnippet ();
		$subscriptionSnippet->setResourceId ( $resourceId );
		
		// Create a subscription request that contains the snippet object.
		$subscription = new Google_Service_YouTube_Subscription ();
		$subscription->setSnippet ( $subscriptionSnippet );
		
		// Execute the request and return an object containing information
		// about the new subscription.
		$subscriptionResponse = $youtube->subscriptions->insert ( 'id,snippet', $subscription, array () );
		
		$htmlBody .= "<h3>Subscription</h3><ul>";
		$htmlBody .= sprintf ( '<li>%s (%s)</li>', $subscriptionResponse ['snippet'] ['title'], $subscriptionResponse ['id'] );
		$htmlBody .= '</ul>';
	} catch ( Google_ServiceException $e ) {
		$htmlBody .= sprintf ( '<p>A service error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	} catch ( Google_Exception $e ) {
		$htmlBody .= sprintf ( '<p>An client error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	}
	
	$_SESSION ['token'] = $client->getAccessToken ();
} else {
	// If the user has not authorized the application, start the OAuth 2.0 flow.
	$state = mt_rand ();
	$client->setState ( $state );
	$_SESSION ['state'] = $state;
	
	$authUrl = $client->createAuthUrl ();
	$htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
<title>Returned Subscription</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html>