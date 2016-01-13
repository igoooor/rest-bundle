<?php

namespace Ibrows\RestBundle\DependencyInjection;

use Ibrows\RestBundle\DependencyInjection\Compiler\OverrideRequestConverterCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\ParamConvertersCompilerPass;
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
        $container->setParameter('ibrows_rest.config.param_converter', $config['param_converter']);

        $container->addCompilerPass(new ParamConvertersCompilerPass());
        $container->addCompilerPass(new OverrideRequestConverterCompilerPass());

        $fileLocator = new FileLocator($configPath);
        $finder = new Finder();

        $loader = new XmlFileLoader($container, $fileLocator);
        /** @var SplFileInfo $xml */
        foreach($finder->in($configPath)->name('*.xml') as $xml){
            $loader->load($xml->getRelativePathname());
        }
    }
}
