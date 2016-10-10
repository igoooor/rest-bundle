<?php
namespace Ibrows\ExampleBundle\Controller;

use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Ibrows\AppBundle\Entity\Car;
use Ibrows\ExampleBundle\Handler\CarHandler;
use Ibrows\ExampleBundle\Handler\CategoryHandler;
use Ibrows\RestBundle\Annotation as IbrowsAPI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class CarController
 *
 * @package Ibrows\ExampleBundle\Controller
 * @FOSRest\Route("/cars", service="example.controller.car")
 * @FOSRest\RouteResource("Car")
 */
class CarController implements ClassResourceInterface
{
    /**
     * @var CarHandler
     */
    private $carHandler;

    /**
     * @var CategoryHandler
     */
    private $categoryHandler;

    /**
     * CarController constructor.
     *
     * @param CarHandler      $carHandler
     * @param CategoryHandler $categoryHandler
     */
    public function __construct(
        CarHandler $carHandler,
        CategoryHandler $categoryHandler
    ) {
        $this->carHandler = $carHandler;
        $this->categoryHandler = $categoryHandler;
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Get list of cars",
     *     resource=true,
     *     output={
     *         "class" = "Ibrows\AppBundle\Entity\Car",
     *         "groups" = { "car_list" }
     *     }
     * )
     *
     * @FOSRest\Get()
     *
     * @param ParamFetcher $paramFetcher
     * @FOSRest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="10",
     *     description="Limit Results"
     * )
     * @FOSRest\QueryParam(
     *     name="offsetId",
     *     requirements="\d+",
     *     description="Last Result"
     * )
     * @FOSRest\QueryParam(
     *     name="sortBy",
     *     requirements="\w+",
     *     default="id",
     *     description="sortBy"
     * )
     * @FOSRest\QueryParam(
     *     name="sortDir",
     *     requirements="\w+",
     *     default="ASC",
     *     description="sort direction"
     * )
     * @FOSRest\QueryParam(
     *     name="expolicy",
     *     requirements="(car_list|car_detail)",
     *     default="car_list",
     *     strict=true,
     *     description="Serialization group"
     * )
     *
     * @IbrowsAPI\View()
     *
     * @return Car[]
     */
    public function cgetAction(ParamFetcher $paramFetcher)
    {
        return $this->carHandler->getList(
            (int)$paramFetcher->get('limit'),
            (int)$paramFetcher->get('offsetId'),
            $paramFetcher->get('sortBy'),
            $paramFetcher->get('sortDir')
        );
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Create a car",
     *     input="Ibrows\AppBundle\Entity\Car"
     * )
     *
     * @FOSRest\Post()
     *
     * @param Car $car
     * @ParamConverter(
     *     "car",
     *     converter="fos_rest.request_body",
     *     class="Ibrows\AppBundle\Entity\Car",
     * )
     *
     * @IbrowsAPI\View(
     *     statusCode=201,
     *     location=@IbrowsAPI\Route(
     *          route="ibrows_example_car_get",
     *          params={
     *              "car"="expr(car.getId())"
     *          }
     *     )
     * )
     *
     */
    public function postAction(Car $car)
    {
        $this->carHandler->create($car);
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Get details of a car",
     *     resource=true,
     *     output={
     *         "class" = "Ibrows\AppBundle\Entity\Car",
     *         "groups" = { "car_detail" },
     *     },
     * )
     *
     * @FOSRest\Get(path="/{car}")
     *
     * @param Car $car
     * @ParamConverter("car", converter="doctrine.orm", class="IbrowsAppBundle:Car")
     *
     * @IbrowsAPI\View(
     *     serializerGroups={ "car_detail" },
     *     cachePolicyName="test1"
     * )
     * @return Car
     */
    public function getAction(Car $car)
    {
        return $car;
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Delete a car",
     * )
     *
     * @FOSRest\Delete(path="/{car}")
     *
     * @param Car $car
     * @ParamConverter("car", converter="doctrine.orm", class="IbrowsAppBundle:Car")
     *
     * @IbrowsAPI\View(
     *     statusCode=204,
     * )
     */
    public function deleteAction(Car $car)
    {
        $this->carHandler->delete($car);
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Edit a car",
     *     input="Ibrows\RestBundle\Ibrows\JsonPatch\Operation",
     * )
     *
     * @FOSRest\Ibrows\JsonPatch(path="/{car}")
     *
     * @param Car $car
     * @ParamConverter(
     *     "car",
     *     converter="patch",
     *     class="IbrowsAppBundle:Car",
     *     options={
     *         "source" = "doctrine.orm",
     *     }
     * )
     *
     * @IbrowsAPI\View(
     *     statusCode=204,
     * )
     */
    public function editAction(Car $car)
    {
        $this->carHandler->update($car);
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Link Resources",
     * )
     *
     * @FOSRest\Link(path="/{car}")
     *
     * @param Car $car
     * @ParamConverter(
     *     "car",
     *     converter="link",
     *     class="IbrowsAppBundle:Car",
     *     options = {
     *         "source" = "doctrine.orm",
     *         "relations" = { "categories" }
     *     },
     * )
     *
     * @IbrowsAPI\View(
     *     statusCode=204,
     * )
     */
    public function linkAction(Car $car)
    {
        $this->carHandler->update($car);
    }

    /**
     * @ApiDoc(
     *     section="Car",
     *     description="Link Resources",
     * )
     *
     * @FOSRest\Unlink(path="/{car}")
     *
     * @param Car $car
     * @ParamConverter(
     *     "car",
     *     converter="unlink",
     *     class="IbrowsAppBundle:Car",
     *     options = {
     *         "source" = "doctrine.orm",
     *         "relations" = { "categories" }
     *     }
     * )
     *
     * @IbrowsAPI\View(
     *     statusCode=204,
     * )
     */
    public function unlinkAction(Car $car)
    {
        $this->carHandler->update($car);
    }
}
