<?php

// This is just a scrap page for developing my thoughts

use WMDE\WikipageUploader\PageUploadRequest;
use WMDE\WikipageUploader\PageUploadUsecase;

$api = new \Mediawiki\Api\MediawikiApi( 'http://localhost/w/api.php' );
$api->login( new \Mediawiki\Api\ApiUser( 'username', 'password' ) );
$services = new \Mediawiki\Api\MediawikiFactory( $api );
$getter = $services->newPageGetter();
$saver = $services->newRevisionSaver();

// TODO  put this in Symfony command

function getRequestFromFilename( $file ) : PageUploadRequest {
	// TODO Build page name from test name, page prefix and file parts
	$pageName = "";
	return new PageUploadRequest(
		$pageName,
		file_get_contents( $file ),
		filemtime( $file )
	);
}

$upload = new PageUploadUsecase( $getter, $saver );
foreach ( glob( '*.html' ) as $file ) {
	$response = $upload->uploadIfChanged( getRequestFromFilename( $file ) );
	// TODO: output response in command
}
