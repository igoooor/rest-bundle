<?php

namespace Ibrows\RestBundle\DependencyInjection;

use Ibrows\RestBundle\DependencyInjection\Compiler\CollectionDecorationListenerCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\DebugViewResponseListenerCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\OverrideRequestConverterCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\ParamConvertersCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\ResourceTransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 *
 * @codeCoverageIgnore
 */
class IbrowsRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $configPath = __DIR__ . '/../Resources/config';

        $container->setParameter('ibrows_rest.config.resources', $config['resources']);
        $container->setParameter('ibrows_rest.config.caches', $config['caches']);
        $container->setParameter('ibrows_rest.config.param_converter', $config['param_converter']);
        $container->setParameter('ibrows_rest.config.listener.exclusion_policy', $config['listener']['exclusion_policy']);
        $container->setParameter('ibrows_rest.config.listener.debug', $config['listener']['debug']);
        $container->setParameter('ibrows_rest.config.listener.collection_decorator', $config['listener']['collection_decorator']);
        $container->setParameter('ibrows_rest.config.listener.etag', $config['listener']['etag']);
        $container->setParameter('ibrows_rest.config.listener.if_none_match', $config['listener']['if_none_match']);
        $container->setParameter('ibrows_rest.config.decorator.paginated', $config['decorator']['paginated']);
        $container->setParameter('ibrows_rest.config.decorator.offset', $config['decorator']['offset']);
        $container->setParameter('ibrows_rest.config.decorator.last_id', $config['decorator']['last_id']);

        $container->addCompilerPass(new ParamConvertersCompilerPass());
        $container->addCompilerPass(new OverrideRequestConverterCompilerPass());
        $container->addCompilerPass(new DebugViewResponseListenerCompilerPass());
        $container->addCompilerPass(new ResourceTransformerCompilerPass());
        $container->addCompilerPass(new CollectionDecorationListenerCompilerPass());

        $fileLocator = new FileLocator($configPath);
        $finder = new Finder();

        $loader = new XmlFileLoader($container, $fileLocator);
        /** @var SplFileInfo $xml */
        foreach($finder->in($configPath)->name('*.xml') as $xml){
            $loader->load($xml->getRelativePathname());
        }
    }
}
