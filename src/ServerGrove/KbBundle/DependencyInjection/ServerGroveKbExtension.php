<?php

namespace ServerGrove\KbBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ServerGroveKbExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('server_grove_kb.article.enable_related_urls', $config['article']['enable_related_urls']);
        $container->setParameter('server_grove_kb.article.front_page_category', $config['article']['front_page_category']);
        $container->setParameter('server_grove_kb.article.front_page_keyword', $config['article']['front_page_keyword']);
        $container->setParameter('server_grove_kb.article.top_keyword', $config['article']['top_keyword']);
        $container->setParameter('server_grove_kb.locales', $config['locales']);
        $container->setParameter('server_grove_kb.default_locale', $config['default_locale']);
        $container->setParameter('server_grove_kb.editor_type', $config['editor_type']);
        $container->setParameter('server_grove_kb.mailer.from.name', $config['mailer']['from']['name']);
        $container->setParameter('server_grove_kb.mailer.from.email', $config['mailer']['from']['email']);
    }
}
