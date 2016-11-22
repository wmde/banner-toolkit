<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit\PageUpload;

class PageUploadResponse {

	private $message;
	private $hasError;
	private $contentChanged;

	private function __construct( string $message, bool $hasError, bool $contentChanged ) {
		$this->message = $message;
		$this->hasError = $hasError;
		$this->contentChanged = $contentChanged;
	}

	public static function newSuccessResponse( $message ): self {
		return new self( $message, false, true );
	}

	public static function newFailureResponse( $message ): self {
		return new self( $message, true, false );
	}

	public static function newNoOpResponse( $message ): self {
		return new self( $message, false, false );
	}

	public function contentHasChanged(): bool {
		return $this->contentChanged;
	}

	public function isSuccess(): bool {
		return !$this->hasError;
	}

	public function getMessage(): string {
		return $this->message;
	}

}
