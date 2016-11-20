<?php

namespace WMDE\Fundraising\BannerWorkflow\Commands;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\BannerWorkflow\CliConfiguration;
use WMDE\Fundraising\BannerWorkflow\FileToPageNameTranslator;
use WMDE\Fundraising\BannerWorkflow\PageUpload\PageUploadRequest;
use WMDE\Fundraising\BannerWorkflow\PageUpload\PageUploadResponse;
use WMDE\Fundraising\BannerWorkflow\PageUpload\PageUploadUseCase;

class UploadCommand extends Command
{
	const BANNER_PAGE_NAME_TEMPLATE = '{{page_prefix}}{{campaign_name}}{{test_name}}_{{variant}}';
	const FILE_PATTERN_REGEX = '/^Banner_(?P<variant>.*).html$/';
	const FILE_GLOB = 'Banner_*.html';

	protected function configure()
	{
		$this->setName( 'upload' )
			->setDescription( 'Upload banner files to wiki' )
			->addOption( 'api_url', null, InputOption::VALUE_REQUIRED, 'API url', 'https://meta.wikimedia.org/w/api.php' )
			->addOption( 'user', 'u', InputOption::VALUE_REQUIRED, 'User name' )
			->addOption( 'password', 'p', InputOption::VALUE_REQUIRED, 'Password' )
			->addOption( 'page_prefix', 'w', InputOption::VALUE_REQUIRED, 'Namespace and page prefix on wiki' )
			->addOption( 'campaign_name', 'c', InputOption::VALUE_REQUIRED, '. This includes a campaign prefix (desktop, mobile, etc)' )
			->addArgument( 'test_name', InputArgument::REQUIRED, 'Test name (without campaign prefix)' );
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		try {
			$config = $this->getConfigFromInput( $input );
		} catch ( InvalidConfigurationException $ex ) {
			$output->writeln( '<error>' .$ex->getMessage() . '</error>' );
			return;
		}

		$fileToPageMapper = $this->createFileToPageMapper( $config, $input->getArgument( 'test_name' ) );
		$useCase = $this->newUseCase( $config );
		foreach ( glob( self::FILE_GLOB ) as $file ) {
			$this->outputResponse(
				$useCase->uploadIfChanged( $this->getRequestFromFilename( $file, $fileToPageMapper ) ),
				$output
			);
		}
	}

	private function newMediawikiServices( string $apiUrl, string $user, string $password ): MediawikiFactory
	{
		$api = new MediawikiApi( $apiUrl );
		$api->login( new ApiUser( $user, $password ) );
		return new MediawikiFactory( $api );
	}

	private function createFileToPageMapper( array $config, string $testName ): FileToPageNameTranslator
	{
		$context = [
			'page_prefix' => $config['page_prefix'],
			'campaign_name' => $config['campaign_name'],
			'test_name' => $testName
		];
		return new FileToPageNameTranslator( self::FILE_PATTERN_REGEX, self::BANNER_PAGE_NAME_TEMPLATE, $context );
	}

	private function getRequestFromFilename( string $file, FileToPageNameTranslator $fileToPageMapper ): PageUploadRequest
	{
		return new PageUploadRequest(
			$fileToPageMapper->getPageName( $file ),
			new \DateTime( '@' . filemtime( $file ) ),
			file_get_contents( $file )
		);
	}

	private function newUseCase( array $config ): PageUploadUseCase
	{
		$services = $this->newMediawikiServices(
			$config['api_url'],
			$config['user'],
			$config['password']
		);
		return new PageUploadUseCase( $services->newPageGetter(), $services->newRevisionSaver() );
	}

	private function outputResponse( PageUploadResponse $response, OutputInterface $output )
	{
		if ( !$response->isSuccess() ) {
			$output->writeln( '<error>' . $response->getMessage() . '</error>' );
			return;
		}
		if ( $response->contentHasChanged() ) {
			$output->writeln( '<info>' . $response->getMessage() . '</info>' );
			return;
		}
		$output->writeln( '<comment>' . $response->getMessage() . '</comment>' );
	}

	private function getConfigFromInput( InputInterface $input ): array
	{
		$processor = new Processor();
		$inputConfig = [
			'api_url' => $input->getOption( 'api_url' ),
			'user' => $input->getOption( 'user' ),
			'password' => $input->getOption( 'password' ),
			'page_prefix' => $input->getOption( 'page_prefix' ),
			'campaign_name' => $input->getOption( 'campaign_name' )
		];
		return $processor->processConfiguration( new CliConfiguration(), [ $inputConfig ] );
	}

}