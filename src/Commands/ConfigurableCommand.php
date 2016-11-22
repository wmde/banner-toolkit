<?php

namespace WMDE\Fundraising\BannerWorkflow\Commands;

use M1\Env\Parser;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use WMDE\Fundraising\BannerWorkflow\CliConfiguration;

class ConfigurableCommand extends Command
{
	protected function getConfigFromInputAndFiles( InputInterface $input ): array
	{
		$processor = new Processor();
		array_filter( $configs = [
			$this->loadConfigValues( $input->getOption( 'config_file' ) ),
			$this->loadConfigValues( '.env' ),
			array_filter( [
				'api_url' => $input->getOption( 'api_url' ),
				'user' => $input->getOption( 'user' ),
				'password' => $input->getOption( 'password' ),
				'page_prefix' => $input->getOption( 'page_prefix' ),
				'campaign_name' => $input->getOption( 'campaign_name' )
			] )
		] );

		print_r($configs);
		return $processor->processConfiguration( new CliConfiguration(), $configs );
	}

	private function loadConfigValues( $name ): array {
		if ( !file_exists( $name ) ) {
			return [];
		}
		$values = [];
		foreach( Parser::parse( file_get_contents( $name ) ) as $k => $v ) {
			$values[strtolower($k)] = $v;
		}
		return $values;
	}
}