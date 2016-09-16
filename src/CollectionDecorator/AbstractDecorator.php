<?php
namespace Ibrows\RestBundle\CollectionDecorator;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Ibrows\RestBundle\Annotation\View;
use Ibrows\RestBundle\Model\ApiListableInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractDecorator implements DecoratorInterface
{
    /**
     * @return string[]
     */
    abstract protected function getInternalParameters();

    /**
     * @param ParameterBag          $params
     * @param ParamFetcherInterface $paramFetcher
     * @return array
     */
    protected function getRouteParameters(ParameterBag $params, ParamFetcherInterface $paramFetcher)
    {
        $routeParameters = [];
        $requiredParameters = $this->getInternalParameters();
        if ($params->has('_template') && $params->get('_template') instanceof View) {
            $requiredParameters = array_merge($params->get('_template')->getRouteParams());
        }
        foreach ($requiredParameters as $routeParamName) {
            if($params->has($routeParamName) &&
                $params->get($routeParamName) instanceof ApiListableInterface) {
                $routeParameters[$routeParamName] = $params->get($routeParamName)->getId();
            } elseif($params->has($routeParamName)) {
                $routeParameters[$routeParamName] = $params->get($routeParamName);
            } elseif (null !== ($value = $paramFetcher->get($routeParamName))) {
                $routeParameters[$routeParamName] = $value;
            }
        }
        return $routeParameters;
    }
}
