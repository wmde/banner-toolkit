<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit\PageUpload;

class PageUploadRequest {
	private $pageName;
	private $lastChange;
	private $newContent;
	private $editMessage;

	public function __construct( string $pageName, \DateTime $lastChange, string $newContent, string $editMessage = '' ) {

		$this->pageName = $pageName;
		$this->lastChange = $lastChange;
		$this->newContent = $newContent;
		$this->editMessage = $editMessage;
	}

	public function getPageName() : string {
		return $this->pageName;
	}

	public function getLastChange() : \DateTime {

		return $this->lastChange;
	}

	public function getNewContent() : string {
		return $this->newContent;
	}

	public function getEditMessage(): string {
		return $this->editMessage;
	}

}
