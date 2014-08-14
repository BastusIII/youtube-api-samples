<?php
$htmlBody = <<<END
<form method="GET">
  <div>
    Search Term: <input type="search" id="q" name="q" placeholder="Enter Search Term">
  </div>
  <div>
    Max Results: <input type="number" id="maxResults" name="maxResults" min="1" max="50" step="1" value="25">
  </div>
  <div>
    Search Type: <select name="type">
	  <option name="">all</option>
	  <option value="video">videos</option>
	  <option value="playlist">playlists</option>
	  <option value="channel">channels</option>
	</select>
  </div>
  <input type="submit" value="Search">
</form>
END;

// This code will execute if the user entered a search query in the form
// and submitted the form. Otherwise, the page displays the form above.
if (isset($_GET ['q']) && $_GET ['q'] && isset($_GET ['maxResults']) && $_GET ['maxResults'] ) {
	// Call set_include_path() as needed to point to your client library.
	set_include_path ( '../../' );
	require_once 'Google/Client.php';
	require_once 'Google/Service/YouTube.php';
	include_once 'config/config.inc.php';
	include_once 'init/simpleKeyInit.inc.php';
	
	try {
		$additionalParams = array (
				'q' => $_GET ['q'],
				'maxResults' => $_GET ['maxResults']
		);
		// with the addidtional parameter type, you can filter the response by resource type
		if(isset($_GET['type']) && !empty($_GET['type'])) {
			$additionalParams['type'] = $_GET['type'];
		}
		// Call the [type].list method to retrieve results matching the specified
		// query term.
		// With the first parameter, the "part" parameter, you can identify which resources you really need for your application
		$searchResponse = $youtube->search->listSearch ( 'id,snippet', $additionalParams );
		
		$videos = '';
		$channels = '';
		$playlists = '';
		$originalResponse = json_encode($searchResponse->toSimpleObject(), JSON_PRETTY_PRINT);
		
		// You can access a property from the json response object via $searchResponse->a->b->c->...
		
		// Add each result to the appropriate list, and then display the lists of
		// matching videos, channels, and playlists.
		foreach ( $searchResponse->items as $searchResult ) {
			// searchResult is from class type Google_Service_YouTube_SearchResult
			$title = $searchResult->snippet->title;
			$thumbnail = '<img height="30px" src="'.$searchResult->snippet->thumbnails->default->url.'"\>';
			
			switch ($searchResult->id->kind) {
				case 'youtube#video' :
					$id = $searchResult->id->videoId;
					$uri = 'http://youtu.be/'.$id;
					$videos .= sprintf ( '<li>%s<a href="%s">%s</a> (%s)</li>', $thumbnail, $uri, $title, $id );
					break;
				case 'youtube#channel' :
					$id = $searchResult->id->channelId;
					$uri = 'http://www.youtube.com/channel/'.$id;
					$channels .= sprintf ( '<li>%s<a href="%s">%s</a> (%s)</li>', $thumbnail, $uri, $title, $id );
					break;
				case 'youtube#playlist' :
					$id = $searchResult->id->playlistId;
					$uri = 'http://www.youtube.com/playlist?list='.$id;
					$playlists .= sprintf ( '<li>%s<a href="%s">%s</a> (%s)</li>', $thumbnail, $uri, $title, $id );
					break;
			}
		}
		
		$htmlBody .= <<<END
    <h3>Videos</h3>
    <ul>$videos</ul>
    <h3>Channels</h3>
    <ul>$channels</ul>
    <h3>Playlists</h3>
    <ul>$playlists</ul>
    <h3>Original Response</h3>
    <pre>$originalResponse</pre>
END;
	} catch ( Google_ServiceException $e ) {
		$htmlBody .= sprintf ( '<p>A service error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	} catch ( Google_Exception $e ) {
		$htmlBody .= sprintf ( '<p>An client error occurred: <code>%s</code></p>', htmlspecialchars ( $e->getMessage () ) );
	}
}
?>

<!doctype html>
<html>
<head>
<title>YouTube Search</title>
</head>
<body>
    <?=$htmlBody?>
  </body>
</html>
