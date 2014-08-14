<?php

$htmlBody = '';

session_start ();

// Call set_include_path() as needed to point to your client library.
set_include_path ( '../../' );
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';
include_once 'config/config.inc.php';
include_once 'init/oAuth2Init.inc.php';

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken ()) {
	try {
		// Execute an API request that lists broadcasts owned by the user who
		// authorized the request.
		$broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts ( 'id,snippet', array (
				'mine' => 'true' 
		) );
		
		$htmlBody .= "<h3>Live Broadcasts</h3><ul>";
		foreach ( $broadcastsResponse ['items'] as $broadcastItem ) {
			$htmlBody .= sprintf ( '<li>%s (%s)</li>', $broadcastItem ['snippet'] ['title'], $broadcastItem ['id'] );
		}
		$htmlBody .= '</ul>';
	} catch ( Google_ServiceException $e ) {
		$htmlBody .= sprintf ( '<p>A service error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	} catch ( Google_Exception $e ) {
		$htmlBody .= sprintf ( '<p>An client error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	}
	
	$_SESSION ['token'] = $client->getAccessToken ();
} else {
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
?>

<!doctype html>
<html>
<head>
<title>My Live Broadcasts</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html>
