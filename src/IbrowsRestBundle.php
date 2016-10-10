<?php
namespace Ibrows\RestBundle;

use Ibrows\RestBundle\DependencyInjection\Compiler\CollectionDecorationListenerCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\DebugResponseListenerCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\OverrideExceptionHandlerCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\OverrideRequestConverterCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\ParamConvertersCompilerPass;
use Ibrows\RestBundle\DependencyInjection\Compiler\ResourceTransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class IbrowsRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ParamConvertersCompilerPass());
        $container->addCompilerPass(new DebugResponseListenerCompilerPass());
        $container->addCompilerPass(new ResourceTransformerCompilerPass());
        $container->addCompilerPass(new CollectionDecorationListenerCompilerPass());
        $container->addCompilerPass(new OverrideExceptionHandlerCompilerPass());
    }
}
