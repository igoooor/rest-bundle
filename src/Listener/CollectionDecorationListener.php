<?php
namespace Ibrows\RestBundle\Listener;

use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations\View;
use Hateoas\Representation\CollectionRepresentation;
use Ibrows\RestBundle\CollectionDecorator\DecoratorInterface;
use Ibrows\RestBundle\Model\ApiListableInterface;
use Ibrows\RestBundle\Transformer\TransformerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class CollectionDecorationListener
{
    /**
     * @var TransformerInterface
     */
    private $resourceTransformer;

    /**
     * @var DecoratorInterface[]
     */
    private $decorators;

    /**
     * AbstractCollectionDecorationListener constructor.
     * @param array $configuration
     * @param TransformerInterface $resourceTransformer
     */
    public function __construct(
        array $configuration,
        TransformerInterface $resourceTransformer
    ) {
        $this->resourceTransformer = $resourceTransformer;
        $this->decorators = [];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (!$this->validateCollection($event->getControllerResult())) {
            return;
        }

        if (!$event->getRequest()->attributes->get('_template') instanceof View) {
            return;
        }

        $this->wrapCollection($event);
        $this->decorateView($event);
        $this->addListGroup($event);
    }

    /**
     * @param $data
     * @return bool isValid
     */
    protected function validateCollection($data)
    {
        if (
            (
                !$data instanceof Collection &&
                !is_array($data)
            )
        ) {
            return false;
        }

        foreach ($data as $item) {
            if (!$item instanceof ApiListableInterface) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    protected function wrapCollection(GetResponseForControllerResultEvent $event)
    {
        $data = $event->getControllerResult();

        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $element = reset($data);
        $name = null;

        if ($element) {
            $name = $this->resourceTransformer->getResourcePluralName($element);
        }

        $event->setControllerResult(
            new CollectionRepresentation(
                $event->getControllerResult(),
                $name
            )
        );
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    protected function decorateView(GetResponseForControllerResultEvent $event)
    {
        $params = $event->getRequest()->attributes;
        array_walk(
            $this->decorators,
            function (DecoratorInterface $decorator) use ($event, $params) {
                $event->setControllerResult(
                    $decorator->decorate(
                        $params,
                        $event->getControllerResult()
                    )
                );
            }
        );
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    private function addListGroup(GetResponseForControllerResultEvent $event)
    {
        /** @var View $view */
        $view = $event->getRequest()->attributes->get('_template');

        if ($view instanceof View &&
            count($view->getSerializerGroups()) > 0
        ) {
            $view->setSerializerGroups(
                array_merge(
                    $view->getSerializerGroups(),
                    [
                        'hateoas_list',
                    ]
                )
            );
        }
    }

    /**
     * @param DecoratorInterface $decorator
     */
    public function addDecorator(DecoratorInterface $decorator)
    {
        $this->decorators[] = $decorator;
    }
}
