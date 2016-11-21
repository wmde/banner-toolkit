<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerWorkflow\PageUpload;

use Mediawiki\Api\Service\PageGetter;
use Mediawiki\Api\Service\RevisionSaver;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\Revision;

class PageUploadUseCase {

	private $pageGetter;

	private $revisionSaver;

	public function __construct( PageGetter $pageGetter, RevisionSaver $revisionSaver ) {

		$this->pageGetter = $pageGetter;
		$this->revisionSaver = $revisionSaver;
	}

	public function uploadIfChanged( PageUploadRequest $request ) : PageUploadResponse {
		$page = $this->pageGetter->getFromTitle( $request->getPageName() );

		if ( empty ( $page->getPageIdentifier()->getId() ) ) {
			return PageUploadResponse::newFailureResponse( sprintf( 'Page \'%s\' does not exist', $request->getPageName() ) );
		}

		$revision = $page->getRevisions()->getLatest();
		$lastModified = ( new \DateTime( $revision->getTimestamp() ) );
		if ( $lastModified >= $request->getLastChange() ) {
			return PageUploadResponse::newNoOpResponse( 'File is older than page' );
		}

		$content = new Content( $request->getNewContent() );
		$newRevision = new Revision( $content, $page->getPageIdentifier() );

		// TODO Add edit comment to PageUploadRequest, create EditInfo object here
		if ( $this->revisionSaver->save( $newRevision ) ) {
			return PageUploadResponse::newSuccessResponse( sprintf( "Content was uploaded to '%s'", $request->getPageName() ) );
		}
		return PageUploadResponse::newFailureResponse( 'Content upload failed' );
	}
}
