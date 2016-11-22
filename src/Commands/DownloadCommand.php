<?php

namespace WMDE\Fundraising\BannerWorkflow\Commands;

use M1\Env\Parser;
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

class DownloadCommand extends ConfigurableCommand
{
	protected function configure()
	{
		$this->setName( 'download' )
			->setDescription( 'Create banner files from wiki' )
			->addOption( 'api_url', null, InputOption::VALUE_REQUIRED, 'API url, e.g. https://meta.wikimedia.org/w/api.php' )
			->addOption( 'user', 'u', InputOption::VALUE_REQUIRED, 'User name' )
			->addOption( 'password', 'p', InputOption::VALUE_REQUIRED, 'Password' )
			->addOption( 'page_prefix', 'w', InputOption::VALUE_REQUIRED, 'Namespace and page prefix on wiki' )
			->addOption( 'config_file', null, InputOption::VALUE_REQUIRED, 'Config file for all options', '.campaign_config' )
			->addOption( 'campaign_name', 'c', InputOption::VALUE_REQUIRED, 'Campaign prefix, e.g. B16WMDE_' )
			->addArgument( 'test_name', InputArgument::REQUIRED, 'Test name (without campaign prefix), e.g. 20_161224\'' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$config = $this->getConfigFromInputAndFiles( $input );
		$services = $this->newMediawikiServices( $config['api_url'], $config['user'], $config['password'] );
		$pages = $services->newPageListGetter()->getFromPrefix( $config['page_prefix'] . $input->getArgument( 'test_name' ) );
		foreach( $pages as $page ) {
			print_r($page);
		}
	}

	private function newMediawikiServices( string $apiUrl, string $user, string $password ): MediawikiFactory
	{
		$api = new MediawikiApi( $apiUrl );
		$api->login( new ApiUser( $user, $password ) );
		return new MediawikiFactory( $api );
	}


}