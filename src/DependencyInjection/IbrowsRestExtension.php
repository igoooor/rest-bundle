<?php
namespace Ibrows\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        $configuration = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('ibrows_rest.config.resource_path_prefixes', $configuration['resources']['path_prefixes']);
        $container->setParameter('ibrows_rest.config.resources_converters', $configuration['resources']['converters']);
        $container->setParameter('ibrows_rest.config.resource_default_converter', $configuration['resources']['default_converter']);
        $container->setParameter('ibrows_rest.config.caches', $configuration['caches']);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new XmlFileLoader($container, $fileLocator);
        $loader->load('collection_decorator.xml');
        $loader->load('debug_converter.xml');
        $loader->load('transformer.xml');
        $loader->load('utils.xml');
        $loader->load('hateoas_configuration_extension.xml');

        // ParamConverters are loaded dynamically according to the configuration.
        foreach ($configuration['param_converter'] as $name => $paramConverter) {
            if ($name !== 'common' &&
                $paramConverter['enabled']
            ) {
                $loader->load('param_converter/' . $name . '.xml');
            }
            $container->setParameter(
                'ibrows_rest.config.param_converter.' . $name,
                array_merge(
                    $configuration['param_converter']['common'],
                    $paramConverter
                )
            );
        }
        // Listeners are loaded dynamically according to the configuration.
        foreach ($configuration['listener'] as $name => $listener) {

            if ($listener['enabled']) {
                $loader->load('listener/' . $name . '.xml');
            }
            $container->setParameter('ibrows_rest.config.listener.' . $name, $listener);
        }

        foreach ($configuration['decorator'] as $name => $decorator) {
            $container->setParameter('ibrows_rest.config.decorator.' . $name, $decorator);
        }
    }
}
