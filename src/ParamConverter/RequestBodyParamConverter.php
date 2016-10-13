<?php
namespace Ibrows\RestBundle\ParamConverter;

use Ibrows\RestBundle\Exception\BadRequestConstraintException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var ParamConverterInterface
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
     * @param ParamConverterInterface $decoratedRequestBodyParamConverter
     * @param array                   $configuration
     */
    public function __construct(
        ParamConverterInterface $decoratedRequestBodyParamConverter,
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

        if ($this->checkFailureOnValidationError($configuration) &&
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

    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    protected function checkFailureOnValidationError(ParamConverter $configuration)
    {
        if (isset($configuration->getOptions()['fail_on_validation_error'])) {
            return $configuration->getOptions()['fail_on_validation_error'];
        }

        return $this->failOnValidationError;
    }
}
