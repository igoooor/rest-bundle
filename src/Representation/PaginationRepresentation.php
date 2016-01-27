<?php
/**
 * Created by PhpStorm.
 * User: fabs
 * Date: 1/13/16
 * Time: 10:29 AM
 */

namespace Ibrows\RestBundle\Representation;

use Hateoas\Configuration\Annotation as Hateoas;
use Hateoas\Configuration\Exclusion;
use Hateoas\Configuration\Relation;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Configuration\Route;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Representation\AbstractSegmentedRepresentation;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

/**
 * Class PaginationRepresentation
 * @package RestBundle\Representation
 *
 * @Hateoas\RelationProvider("getRelations")
 */
class PaginationRepresentation extends PaginatedRepresentation
{
    /**
     * @var Exclusion
     */
    private $exclusion;

    public function __construct($inline, $route, array $parameters = array(), $page, $limit, $pages, $pageParameterName = null, $limitParameterName = null, $absolute = false, $total = null, Exclusion $exclusion = null)
    {
        if ($exclusion === null) {
            $exclusion = new Exclusion(
                [
                    'hateoas_list'
                ]
            );
        }

        parent::__construct($inline, $route, $parameters, $page, $limit, $pages, $pageParameterName, $limitParameterName, $absolute, $total);

        $this->exclusion = $exclusion;
    }


    /**
     * @param OffsetRepresentation   $object
     * @param ClassMetadataInterface $classMetadata
     * @return Relation[]
     * @codeCoverageIgnore
     */
    public function getRelations($object, ClassMetadataInterface $classMetadata)
    {
        return [
            new Relation(
                'first',
                new Route(
                    'expr(object.getRoute())',
                    'expr(object.getParameters(0))',
                    'expr(object.isAbsolute())'
                ),
                null,
                [],
                $this->exclusion
            ),
            new Relation(
                'last',
                new Route(
                    'expr(object.getRoute())',
                    'expr(object.getParameters(object.getPages()))',
                    'expr(object.isAbsolute())'
                ),
                null,
                [],
                new Exclusion(
                    $this->exclusion->getGroups(),
                    $this->exclusion->getSinceVersion(),
                    $this->exclusion->getUntilVersion(),
                    $this->exclusion->getMaxDepth(),
                    'expr(object.getPages() === null)'
                )
            ),
            new Relation(
                'next',
                new Route(
                    'expr(object.getRoute())',
                    'xpr(object.getParameters(object.getPage() + 1))',
                    'expr(object.isAbsolute())'
                ),
                null,
                [],
                new Exclusion(
                    $this->exclusion->getGroups(),
                    $this->exclusion->getSinceVersion(),
                    $this->exclusion->getUntilVersion(),
                    $this->exclusion->getMaxDepth(),
                    'expr(object.getPages() !== null && (object.getPage() + 1) > object.getPages())'
                )
            ),
            new Relation(
                'previous',
                new Route(
                    'expr(object.getRoute())',
                    'xpr(object.getParameters(object.getPage() - 1))',
                    'expr(object.isAbsolute())'
                ),
                null,
                [],
                new Exclusion(
                    $this->exclusion->getGroups(),
                    $this->exclusion->getSinceVersion(),
                    $this->exclusion->getUntilVersion(),
                    $this->exclusion->getMaxDepth(),
                    'expr((object.getPage() - 1) < 1)'
                )
            ),
        ];
    }
}
