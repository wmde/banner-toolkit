<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerWorkflow\PageUpload;

class PageUploadRequest {
	private $pageName;
	private $lastChange;
	private $newContent;

	public function __construct( string $pageName, int $lastChange, string $newContent ) {

		$this->pageName = $pageName;
		$this->lastChange = $lastChange;
		$this->newContent = $newContent;
	}

	public function getPageName() : string {
		return $this->pageName;
	}

	public function getLastChange() : int {

		return $this->lastChange;
	}

	public function getNewContent() : string {
		return $this->newContent;
	}

}
