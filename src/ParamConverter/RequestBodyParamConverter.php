<?php
namespace Ibrows\RestBundle\ParamConverter;

use FOS\RestBundle\Request\RequestBodyParamConverter as BaseRequestBodyParamConverter;
use Ibrows\RestBundle\Exception\BadRequestConstraintException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var BaseRequestBodyParamConverter
     */
    private $decoratedRequestBodyParamConverter;

    /**
     * @var string
     */
    private $validationErrorsArgument;

    /**
     * @var boolean
     */
    private $failOnValidationError;

    /**
     * RequestBodyParamConverter constructor.
     * @param BaseRequestBodyParamConverter $decoratedRequestBodyParamConverter
     * @param array                         $configuration
     */
    public function __construct(
        BaseRequestBodyParamConverter $decoratedRequestBodyParamConverter,
        array $configuration
    ) {
        $this->decoratedRequestBodyParamConverter = $decoratedRequestBodyParamConverter;
        $this->validationErrorsArgument = $configuration['validation_errors_argument'];
        $this->failOnValidationError = $configuration['fail_on_validation_error'];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $value = $this->decoratedRequestBodyParamConverter->apply($request, $configuration);

        $validationErrors = $request->attributes->get($this->validationErrorsArgument);

        if (
            $this->failOnValidationError &&
            count($validationErrors) > 0
        ) {
            throw new BadRequestConstraintException($validationErrors);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $this->decoratedRequestBodyParamConverter->supports($configuration);
    }
}