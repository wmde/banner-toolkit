<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Pages;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Revisions;
use Mediawiki\DataModel\Title;
use RuntimeException;

class PageRangeGetter
{
	private $api;

	public function __construct( MediawikiApi $api )
	{
		$this->api = $api;
	}


	public function getPageRange( int $namespace, string $startFrom = '', string $listTo = ''  ) {
		$pages = new Pages();
		$params = [
			'prop' => 'revisions',
			'generator' => 'allpages',
			'gapfrom' => $startFrom,
			'gapto' => $listTo,
			'gapnamespace' => $namespace,
			'gaplimit' => 20
		];
		$result = $this->api->getRequest( new SimpleRequest( 'query', $params ) );
		if ( !array_key_exists( 'query', $result ) ) {
			return $pages;
		}

		foreach ( $result['query']['pages'] as $member ) {
			$pages->addPage(
				new Page(
					new PageIdentifier(
						new Title( $member['title'], $member['ns'] ),
						$member['pageid']
					),
					$this->getRevisionsFromResult( $member )
				)
			);
		}
		return $pages;
	}

	/**
	 * @param array $array
	 *
	 * @return Revisions
	 */
	private function getRevisionsFromResult( $array ) {
		$revisions = new Revisions();
		$pageid = $array['pageid'];
		foreach ( $array['revisions'] as $revision ) {
			$revisions->addRevision(
				new Revision(
					$this->getContent( $array['contentmodel'], $revision['*'] ),
					new PageIdentifier( new Title( $array['title'], $array['ns'] ), $pageid ),
					$revision['revid'],
					new EditInfo(
						$revision['comment'],
						array_key_exists( 'minor', $revision ),
						array_key_exists( 'bot', $revision )
					),
					$revision['user'],
					$revision['timestamp']
				)
			);
		}

		return $revisions;
	}

	/**
	 * @param string $model
	 * @param string $content returned from the API
	 *
	 * @throws RuntimeException
	 * @return Content
	 */
	private function getContent( $model, $content ) {
		return new Content( $content, $model );
	}

}