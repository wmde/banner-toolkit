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
		$lastModified = ( new \DateTime( $revision->getTimestamp() ) )->format( 'U' );
		if ( $lastModified >= $request->getLastChange() ) {
			return PageUploadResponse::newNoOpResponse( 'File is older than page' );
		}

		$content = new Content( $request->getNewContent() );
		$newRevision = new Revision( $content, $page->getPageIdentifier() );

		// TODO create EditInfo with get change comment from last git commit
		if ( $this->revisionSaver->save( $newRevision ) ) {
			return PageUploadResponse::newSuccessResponse( 'Content was uploaded' );
		}
		return PageUploadResponse::newFailureResponse( 'Content upload failed' );
	}
}
