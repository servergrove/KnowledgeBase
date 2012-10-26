<?php

namespace ServerGrove\KbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('server_grove_kb');

        $rootNode
            ->children()
                ->arrayNode('locales')
                ->prototype('scalar')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
            ->end();

        $rootNode
            ->children()
                ->arrayNode('article')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enable_related_urls')->defaultFalse()->end()
                        ->scalarNode('front_page_category')->defaultValue('Homepage')->end()
                        ->scalarNode('front_page_keyword')->defaultValue('homepage')->end()
                        ->scalarNode('top_keyword')->defaultValue('feature')->end()
                    ->end()
                ->end()
            ->end();

        $rootNode
            ->children()
                ->scalarNode('default_locale')
                ->cannotBeEmpty()
                ->defaultValue('en')
                ->end()
            ->end();

        $rootNode
            ->children()
                ->scalarNode('editor_type')
                ->cannotBeEmpty()
                ->defaultValue('markdown')
                ->end()
            ->end();

        $rootNode
            ->children()
                ->arrayNode('mailer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('from')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('email')->defaultValue('no-reply@servergrove.com')->end()
                                ->scalarNode('name')->defaultValue('ServerGrove KnowledgeBase System')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
