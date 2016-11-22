<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit;

class FileToPageNameTranslator
{
	private $filePartRx;
	private $pageNameTemplate;
	private $context;

	public function __construct( $filePartRx, $pageNameTemplate, array $context = [] )
	{
		$this->filePartRx = $filePartRx;
		$this->pageNameTemplate = $pageNameTemplate;
		$this->context = $context;
	}

	public function getPageName( string $fileName ): string
	{
		preg_match( $this->filePartRx, $fileName, $matches );
		$values = array_replace( $this->getEmptyValuesForTemplate(), $this->context, $matches );
		return strtr( $this->pageNameTemplate, $this->getPlaceholders( $values ) );
	}

	private function getPlaceholders( $values ): array {
		$placeholders = [];
		foreach ( $values as $k => $v ) {
			$placeholders['{{' . $k . '}}'] = $v;
		}
		return $placeholders;
	}

	private function getEmptyValuesForTemplate(): array {
		preg_match_all( '/\{\{(\w+)\}\}/', $this->pageNameTemplate, $matches );
		$emptyValues = [];
		foreach ( $matches[1] as $placeholder ) {
			$emptyValues[(string) $placeholder] = '';
		}
		return $emptyValues;
	}

}