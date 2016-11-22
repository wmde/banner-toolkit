<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit\Test;

use WMDE\Fundraising\BannerToolkit\FileToPageNameTranslator;

class FileToPageNameTranslatorTest extends \PHPUnit_Framework_TestCase
{
	public function testFilePartsAreExtractedToPlaceholders() {
		$translator = new FileToPageNameTranslator( '/^Banner(.*)\.html$/', 'B16WMDE{{1}}' );
		$this->assertSame( 'B16WMDE_test', $translator->getPageName( 'Banner_test.html' ) );
	}

	public function testFilePartsCanContainNamedPlaceholders() {
		$translator = new FileToPageNameTranslator( '/^Banner(?P<variant>.*)\.html$/', 'B16WMDE{{variant}}' );
		$this->assertSame( 'B16WMDE_test', $translator->getPageName( 'Banner_test.html' ) );
	}

	public function testMissingPlaceholdersAreRemoved() {
		$translator = new FileToPageNameTranslator( '/^Banner(.*)\.html$/', 'B16WMDE{{1}}{{2}}{{foo}}' );
		$this->assertSame( 'B16WMDE_test', $translator->getPageName( 'Banner_test.html' ) );
	}

	public function testAdditionalContextValuesAreInsertedIntoPlaceholders() {
		$context = [
			'campaign' => 'B16WMDE_mobile',
			'name' => '_01'
		];
		$translator = new FileToPageNameTranslator( '/^Banner(.*)\.html$/', '{{campaign}}{{1}}{{2}}{{name}}', $context );
		$this->assertSame( 'B16WMDE_mobile_test_01', $translator->getPageName( 'Banner_test.html' ) );
	}

}
