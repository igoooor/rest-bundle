<?php
namespace Ibrows\RestBundle\Listener;

use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\AbstractSegmentedRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Ibrows\RestBundle\Annotation\View as IbrowsView;
use Ibrows\RestBundle\Model\ApiListableInterface;
use Ibrows\RestBundle\Representation\CollectionRepresentation;
use Ibrows\RestBundle\Representation\LastIdRepresentation;
use Ibrows\RestBundle\Representation\OffsetRepresentation;
use Ibrows\RestBundle\Representation\PaginationRepresentation;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class CollectionViewResponseListener
{
    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if(
            $event->getControllerResult() instanceof Collection ||
            is_array($event->getControllerResult())
        ) {
            $this->decorateView($event);
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    private function decorateView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();
        $route = $event->getRequest()->attributes->get('_route');
        $response = new CollectionRepresentation($response);
        /** @var ParamFetcherInterface $paramFetcher */
        $paramFetcher = $event->getRequest()->attributes->get('paramFetcher');
        /** @var View $view */
        $view = $event->getRequest()->attributes->get('_view');
        $params = $this->getParams($view, $event->getRequest());

        $this->addListGroup($view);
        $this->decorateOffsetView($paramFetcher, $route, $params, $response);
        $this->decorateLastIdView($paramFetcher, $route, $params, $response);
        $this->decoratePaginatedView($paramFetcher, $route, $params, $response);
        $event->setControllerResult($response);
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param string                $route
     * @param array                 $params
     * @param mixed                 $response
     */
    private function decorateOffsetView(ParamFetcherInterface $paramFetcher, $route, array $params, & $response)
    {
        try {
            $limit = (int) $paramFetcher->get('limit');
            $offset = (int) $paramFetcher->get('offset');

            $response = new OffsetRepresentation(
                $response,
                $route,
                $params,
                $offset,
                $limit
            );
        } catch(InvalidArgumentException $e) {
            // There is no way to check if a param exists without catching an exception
        }
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param string                $route
     * @param array                 $params
     * @param mixed                 $response
     */
    private function decorateLastIdView(ParamFetcherInterface $paramFetcher, $route, array $params, &$response)
    {
        if(!$this->hasParameter($paramFetcher, 'limit') || !$this->hasParameter($paramFetcher, 'offsetId') ){
            return;
        }

        $limit = $this->getParameter($paramFetcher, 'limit');
        $last = $this->getParameter($paramFetcher, 'offsetId');
        $sortBy = $this->getParameter($paramFetcher, 'sortBy');
        $sortDir = $this->getParameter($paramFetcher, 'sortDir');

        $resources = $response->getResources();
        $lastElement = end($resources);

        if(!$lastElement || !$lastElement instanceof ApiListableInterface) {
            return;
        }

        $response = new LastIdRepresentation(
            $response,
            $route,
            $params,
            $lastElement->getId(),
            'offsetId',
            $limit,
            'limit',
            $sortBy,
            $sortDir
        );
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param string                $route
     * @param array                 $params
     * @param mixed                 $response
     */
    private function decoratePaginatedView(ParamFetcherInterface $paramFetcher, $route, array $params, & $response)
    {

        if(!$this->hasParameter($paramFetcher, 'limit') || !$this->hasParameter($paramFetcher, 'page') ){
            return;
        }

        $limit = $this->getParameter($paramFetcher, 'limit');
        $page = $this->getParameter($paramFetcher, 'page');

        if(($limit === null || $page === null)){
            return;
        }

        $response = new PaginatedRepresentation(
            $response,
            $route,
            $params,
            $page,
            $limit
        );
    }

    /**
     * @param View $view
     */
    private function addListGroup(View $view)
    {
        if(
            $view &&
            count($view->getSerializerGroups()) > 0
        ) {
            $view->setSerializerGroups(array_merge(
                $view->getSerializerGroups(),
                [
                    'hateoas_list',
                ]
            ));
        }
    }

    /**
     * @param View    $view
     * @param Request $request
     *
     * @return array
     */
    private function getParams(View $view, Request $request)
    {
        $params = [];
        if(
            $view instanceof IbrowsView &&
            is_array($view->getRouteParams())
        ) {
            /** @var IbrowsView $view */
            foreach($view->getRouteParams() as $paramName) {
                $param = $request->get($paramName);
                if(
                    is_object($param) &&
                    method_exists($param, 'getId')
                ) {
                    $param = $param->getId();
                }
                $params[$paramName] = $param;
            }
        }
        return $params;
    }


    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param $key
     * @return null
     */
    protected function getParameter(ParamFetcherInterface $paramFetcher, $key){
        $params = $paramFetcher->all();
        if(isset($params[$key])){
            return $params[$key];
        }
        return null;
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param $key
     * @return null
     */
    protected function hasParameter(ParamFetcherInterface $paramFetcher, $key){
        $params = $paramFetcher->all();

        return array_key_exists($key, $params);
    }
}