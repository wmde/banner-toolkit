<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit\Test;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use WMDE\Fundraising\BannerToolkit\CliConfiguration;

class CliConfigurationTest extends \PHPUnit_Framework_TestCase
{
	public function testConfigurationArraysAreMerged() {
		$processor = new Processor();
		$configuration = new CliConfiguration();
		$credentials = [
			'password' => 's33cr3t',
			'user' => 'nemo'
		];
		$settings = [
			'api_url' => 'http://localhost/w/api.php',
			'page_prefix' => 'Web:Banner/',
			'campaign_name' => 'B16WMDE'
		];
		$config = $processor->processConfiguration(
			$configuration,
			[ $credentials, $settings ]
		);
		$expectedConfig = [
			'password' => 's33cr3t',
			'user' => 'nemo',
			'api_url' => 'http://localhost/w/api.php',
			'page_prefix' => 'Web:Banner/',
			'campaign_name' => 'B16WMDE'
		];
		$this->assertEquals( $expectedConfig, $config );
	}

	public function testApiUrlIsRequired() {
		$processor = new Processor();
		$configuration = new CliConfiguration();
		try {
			$processor->processConfiguration(
				$configuration,
				[ [
					'api_url' => '',
					'password' => 's33cr3t',
					'user' => 'nemo'
				] ]
			);
			$this->fail( 'Missing API URL should throw an exception' );
		} catch ( InvalidConfigurationException $e ) {
			$this->assertContains( 'cli.api_url', $e->getMessage() );
		}
	}

	public function testUsernameIsRequired() {
		$processor = new Processor();
		$configuration = new CliConfiguration();
		try {
			$processor->processConfiguration(
				$configuration,
				[ [
					'api_url' => 'http://localhost/w/api.php',
					'password' => 's33cr3t',
					'user' => ''
				] ]
			);
			$this->fail( 'Missing user name should throw an exception' );
		} catch ( InvalidConfigurationException $e ) {
			$this->assertContains( 'cli.user', $e->getMessage() );
		}
	}

	public function testPasswordIsRequired() {
		$processor = new Processor();
		$configuration = new CliConfiguration();
		try {
			$processor->processConfiguration(
				$configuration,
				[ [
					'api_url' => 'http://localhost/w/api.php',
					'password' => '',
					'user' => 'nemo'
				] ]
			);
			$this->fail( 'Missing password should throw an exception' );
		} catch ( InvalidConfigurationException $e ) {
			$this->assertContains( 'cli.password', $e->getMessage() );
		}
	}
}
