<?php
/**
 * Created by PhpStorm.
 * User: fabs
 * Date: 1/14/16
 * Time: 3:24 PM
 */

namespace Ibrows\RestBundle\Tests\Listener;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Ibrows\RestBundle\Listener\CollectionViewResponseListener;
use Ibrows\RestBundle\Model\ApiListableInterface;
use Ibrows\RestBundle\Representation\LastIdRepresentation;
use Ibrows\RestBundle\Representation\OffsetRepresentation;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CollectionViewResponseListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpKernelInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = $this->getMockForAbstractClass(HttpKernelInterface::class);
    }

    public function testLastIdDecoration()
    {
        $listener = $this->getListener();

        $paramFetcher = $this->getMockForAbstractClass(ParamFetcherInterface::class);
        $paramFetcher->method('all')->willReturn(array('limit' => 10, 'offsetId' => 1, 'sortBy' => 'id', 'sortDir' => 'ASC'));
        $paramFetcher->method('get')->willThrowException(new \InvalidArgumentException());

        $listEntity = $this->getMockForAbstractClass(ApiListableInterface::class);
        $listEntity->method('getId')->willReturn(42);

        $event = $this->getEvent($paramFetcher, array($listEntity));

        $listener->onKernelView($event);

        $this->assertTrue($event->getControllerResult() instanceof LastIdRepresentation);
    }


    public function testOffsetDecoration(){
        $listener = $this->getListener();

        $paramFetcher = $this->getMockForAbstractClass(ParamFetcherInterface::class);
        $paramFetcher->method('all')->willReturn(array('limit' => 10, 'offset' => 5));
        $paramFetcher->method('get')->willThrowException(new \InvalidArgumentException());

        $listEntity = $this->getMockForAbstractClass(ApiListableInterface::class);
        $listEntity->method('getId')->willReturn(42);

        $event = $this->getEvent($paramFetcher, array($listEntity));

        $listener->onKernelView($event);

        $this->assertTrue($event->getControllerResult() instanceof OffsetRepresentation);
    }


    public function testPaginationDecoration(){
        $listener = $this->getListener();

        $paramFetcher = $this->getMockForAbstractClass(ParamFetcherInterface::class);
        $paramFetcher->method('all')->willReturn(array('limit' => 10, 'page' => 5));
        $paramFetcher->method('get')->willThrowException(new \InvalidArgumentException());

        $listEntity = $this->getMockForAbstractClass(ApiListableInterface::class);
        $listEntity->method('getId')->willReturn(42);

        $event = $this->getEvent($paramFetcher, array($listEntity));

        $listener->onKernelView($event);

        $this->assertTrue($event->getControllerResult() instanceof PaginatedRepresentation);
    }



    protected function getListener(){
        return new CollectionViewResponseListener();
    }

    protected function getEvent(ParamFetcherInterface $paramFetcher = null, $controllerResult = array()){
        $view = new View(array());

        $request = new Request(array(), array(), array('_view' => $view, 'paramFetcher' => $paramFetcher, '_route' => 'foo'));

        return new GetResponseForControllerResultEvent($this->kernel,  $request, HttpKernelInterface::MASTER_REQUEST, $controllerResult);
    }
}