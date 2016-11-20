<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerWorkflow\Test;

use Mediawiki\Api\Service\PageGetter;
use Mediawiki\Api\Service\RevisionSaver;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
use WMDE\Fundraising\BannerWorkflow\PageUpload\PageUploadRequest;
use WMDE\Fundraising\BannerWorkflow\PageUpload\PageUploadUseCase;

class PageUploadUseCaseTest extends \PHPUnit_Framework_TestCase
{
	const LAST_CHANGE_TIMESTAMP = '2016-11-20T0:00:00Z';
	const BANNER_NAME = 'B16WMDE_01_161120_test';
	const BANNER_PAGE_ID = 1;

	public function testGivenNonExistingPage_errorResponseIsCreated() {
		$getter = $this->getMockBuilder( PageGetter::class )->disableOriginalConstructor()->getMock();
		$saver = $this->getMockBuilder( RevisionSaver::class )->disableOriginalConstructor()->getMock();
		$useCase = new PageUploadUseCase( $getter, $saver );
		$getter->method( 'getFromTitle' )
			->willReturn( $this->newNonexistingPage() );

		$this->assertFalse( $useCase->uploadIfChanged( $this->newRequest() )->isSuccess() );
	}

	private function newNonexistingPage(): Page {
		return new Page( new PageIdentifier( null, 0 ) );
	}

	private function newRequest(): PageUploadRequest {
		return new PageUploadRequest( self::BANNER_NAME, new \DateTime( self::LAST_CHANGE_TIMESTAMP ), 'New Banner' );
	}

	public function testGivenUnchangedPage_noOpResponseIsCreated() {
		$getter = $this->getMockBuilder( PageGetter::class )->disableOriginalConstructor()->getMock();
		$saver = $this->getMockBuilder( RevisionSaver::class )->disableOriginalConstructor()->getMock();
		$useCase = new PageUploadUseCase( $getter, $saver );
		$getter->method( 'getFromTitle' )
			->willReturn( $this->createPageWithDate( new \DateTime( self::LAST_CHANGE_TIMESTAMP ) ) );

		$this->assertFalse( $useCase->uploadIfChanged( $this->newRequest() )->contentHasChanged() );
	}

	public function testGivenChangeTimestampDifferenceIsTooLowDueToTimezone_noOpResponseIsCreated() {
		$getter = $this->getMockBuilder( PageGetter::class )->disableOriginalConstructor()->getMock();
		$saver = $this->getMockBuilder( RevisionSaver::class )->disableOriginalConstructor()->getMock();
		$useCase = new PageUploadUseCase( $getter, $saver );
		$getter->method( 'getFromTitle' )
			->willReturn( $this->createPageWithDate( new \DateTime( self::LAST_CHANGE_TIMESTAMP ) ) );

		$lastChange = new \DateTime( self::LAST_CHANGE_TIMESTAMP, new \DateTimeZone( 'Europe/Berlin' ) );
		$lastChange->modify( '+ 10 minutes' );
		$request = new PageUploadRequest( self::BANNER_NAME, $lastChange, 'New Banner' );
		$this->assertFalse( $useCase->uploadIfChanged( $request )->contentHasChanged() );
	}

	private function createPageWithDate( \DateTime $lastChangeTimestamp ): Page {
		$identifier = new PageIdentifier( new Title( self::BANNER_NAME ), self::BANNER_PAGE_ID );
		$page = new Page( $identifier );
		$content = new Content( 'Old Banner' );
		$page->getRevisions()->addRevision( new Revision( $content, $identifier, null, null, null, $lastChangeTimestamp->format( 'Y-m-d\TH:i:s\Z' ) ) );
		return $page;
	}

	public function testGivenChangedPage_successReponseIsCreated() {
		$getter = $this->getMockBuilder( PageGetter::class )->disableOriginalConstructor()->getMock();
		$saver = $this->getMockBuilder( RevisionSaver::class )->disableOriginalConstructor()->getMock();
		$saver->method( 'save' )->willReturn( true );
		$useCase = new PageUploadUseCase( $getter, $saver );
		$getter->method( 'getFromTitle' )
			->willReturn( $this->createPageWithDate( ( new \DateTime( self::LAST_CHANGE_TIMESTAMP ) )->modify( '-10 minutes' ) ) );

		$this->assertTrue( $useCase->uploadIfChanged( $this->newRequest() )->contentHasChanged() );
	}

	public function testFailingUpload_successResponseIsCreated() {
		$getter = $this->getMockBuilder( PageGetter::class )->disableOriginalConstructor()->getMock();
		$saver = $this->getMockBuilder( RevisionSaver::class )->disableOriginalConstructor()->getMock();
		$saver->method( 'save' )->willReturn( false );
		$useCase = new PageUploadUseCase( $getter, $saver );
		$getter->method( 'getFromTitle' )
			->willReturn( $this->createPageWithDate( ( new \DateTime( self::LAST_CHANGE_TIMESTAMP ) )->modify( '-10 minutes' ) ) );

		$this->assertFalse( $useCase->uploadIfChanged( $this->newRequest() )->contentHasChanged() );
		$this->assertFalse( $useCase->uploadIfChanged( $this->newRequest() )->isSuccess() );
	}
}
