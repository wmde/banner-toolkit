<?php

namespace WMDE\Fundraising\BannerWorkflow\Commands;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
		$fileToPageMapper = $this->createFileToPageMapper( $input );
		$useCase = $this->newUseCaseFromInput( $input );
		foreach ( glob( self::FILE_GLOB ) as $file ) {
			$this->outputResponse(
				$useCase->uploadIfChanged( $this->getRequestFromFilename( $file, $fileToPageMapper, $input ) ),
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

	private function createFileToPageMapper( InputInterface $input ): FileToPageNameTranslator
	{
		$context = [
			'page_prefix' => $input->getOption( 'page_prefix' ),
			'campaign_name' => $input->getOption( 'campaign_name' ),
			'test_name' => $input->getArgument( 'test_name' )
		];
		return new FileToPageNameTranslator( self::FILE_PATTERN_REGEX, self::BANNER_PAGE_NAME_TEMPLATE, $context );
	}

	private function getRequestFromFilename( string $file, FileToPageNameTranslator $fileToPageMapper, InputInterface $input ): PageUploadRequest
	{
		return new PageUploadRequest(
			$fileToPageMapper->getPageName( $file ),
			filemtime( $file ),
			file_get_contents( $file )
		);
	}

	private function newUseCaseFromInput( InputInterface $input ): PageUploadUseCase
	{
		$services = $this->newMediawikiServices(
			$input->getOption( 'api_url' ),
			$input->getOption( 'user' ),
			$input->getOption( 'password' )
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

}