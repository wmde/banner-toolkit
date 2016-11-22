<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\BannerToolkit;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class CliConfiguration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root( 'cli' );
		$rootNode->children()
			->scalarNode( 'api_url' )
				->info( 'Mediawiki API URL' )
				->isRequired()
				->cannotBeEmpty()
				->defaultValue( 'https://meta.wikimedia.org/w/api.php' )
			->end()
			->scalarNode( 'user' )
				->info( 'User Name for API usage' )
				->isRequired()
				->cannotBeEmpty()
			->end()
			->scalarNode( 'password' )
				->info( 'Password for API usage' )
				->isRequired()
				->cannotBeEmpty()
			->end()
			->scalarNode( 'page_prefix' )
				->info( 'Namespace and page prefix on wiki' )
				->defaultValue( '' )
			->end()
			->scalarNode( 'campaign_name' )
				->info( 'Campaign prefix for each Banner, e.g. B16WMDE_, B16WMDE_mob_, B16WMDE_EN_, etc' )
				->defaultValue( '' )
			->end()
		->end();
		return $treeBuilder;
	}
}