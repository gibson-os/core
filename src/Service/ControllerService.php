<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ExceptionResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use JsonException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class ControllerService
{
    public function __construct(private ServiceManagerService $serviceManagerService, private RequestService $requestService, private StatusCode $statusCode, private TwigService $twigService, private EnvService $envService)
    {
    }

    public function runAction(): void
    {
        $controllerName = $this->getControllerClassname();
        $action = $this->requestService->getActionName();

        try {
            $controller = $this->serviceManagerService->get($controllerName);
            /** @psalm-suppress ArgumentTypeCoercion */
            $reflectionClass = new ReflectionClass($controllerName);
        } catch (ReflectionException|FactoryError $e) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Controller %s not found!', $controllerName), 404, $e),
                $this->requestService,
                $this->twigService
            ));

            return;
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($action);
        } catch (ReflectionException $e) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s not exists!', $controllerName, $action), 404, $e),
                $this->requestService,
                $this->twigService
            ));

            return;
        }

        if (!$reflectionMethod->isPublic()) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s is not public!', $controllerName, $action), 405),
                $this->requestService,
                $this->twigService
            ));

            return;
        }

        try {
            $attributes = $this->getAttributes($reflectionMethod);
            $parameters = $this->getParameters($reflectionMethod, $attributes);
            $parameters = $this->preExecuteAttributes($attributes, $parameters, $reflectionMethod->getParameters());
            $parameters = $this->cleanParameters($reflectionMethod, $parameters);
            /** @var ResponseInterface $response */
            $response = $controller->$action(...$parameters);
            $this->postExecuteAttributes($attributes, $response);

            try {
                $this->checkRequiredHeaders($response);
            } catch (ControllerError $e) {
                if (!$response instanceof AjaxResponse) {
                    throw $e;
                }

                $response = $this->renderTemplate();
            }
        } catch (Throwable $e) {
            $response = new ExceptionResponse($e, $this->requestService, $this->twigService);
        }

        $this->outputResponse($response);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     */
    private function renderTemplate(): TwigResponse
    {
        $now = time();
        $context = [
            'baseDir' => preg_replace('|^(.*/).+?$|', '$1', $_SERVER['SCRIPT_NAME']),
            'domain' => strtolower($_SERVER['REQUEST_SCHEME']) . '://' . $_SERVER['HTTP_HOST'],
            'serverDate' => [
                'now' => $now,
                'sunrise' => date_sunrise(
                    $now,
                    SUNFUNCS_RET_TIMESTAMP,
                    $this->envService->getFloat('DATE_LATITUDE'),
                    $this->envService->getFloat('DATE_LONGITUDE')
                ),
                'sunset' => date_sunset(
                    $now,
                    SUNFUNCS_RET_TIMESTAMP,
                    $this->envService->getFloat('DATE_LATITUDE'),
                    $this->envService->getFloat('DATE_LONGITUDE')
                ),
            ],
            'request' => $this->requestService,
            'session' => $this->serviceManagerService->get(SessionService::class),
        ];

        return (new TwigResponse($this->twigService, '@core/base.html.twig'))
            ->setVariables($context)
        ;
    }

    private function outputResponse(ResponseInterface $response): void
    {
        header($this->statusCode->getStatusHeader($response->getCode()));

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            if (!is_array($headerValues)) {
                $headerValues = [$headerValues];
            }

            foreach ($headerValues as $index => $headerValue) {
                header($headerName . ': ' . $headerValue, $index === 0);
            }
        }

        echo $response->getBody();
    }

    /**
     * @throws ControllerError
     */
    private function checkRequiredHeaders(ResponseInterface $response): void
    {
        foreach ($response->getRequiredHeaders() as $requiredHeader => $requiredValue) {
            try {
                $headerValue = $this->requestService->getHeader($requiredHeader);
            } catch (RequestError $e) {
                throw new ControllerError(sprintf('Required header %s not exists!', $requiredHeader), 0, $e);
            }

            if ($requiredValue !== $headerValue) {
                throw new ControllerError(sprintf(
                    'Required header %s has value %s. Required value is %s!',
                    $requiredHeader,
                    $headerValue,
                    $requiredValue
                ));
            }
        }
    }

    /**
     * @param array<array-key, array{service: AbstractActionAttributeService, attribute: AttributeInterface}> $attributes
     *
     * @throws ControllerError
     * @throws JsonException
     */
    private function getParameters(ReflectionMethod $reflectionMethod, array $attributes): array
    {
        $parameters = [];
        $attributeParameters = [];

        /** @var array{service: AbstractActionAttributeService, attribute: AttributeInterface} $attribute */
        foreach ($attributes as $attribute) {
            $attributeParameters = array_merge(
                $attributeParameters,
                $attribute['service']->usedParameters($attribute['attribute'])
            );
        }

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass instanceof ReflectionClass) {
                try {
                    $parameters[$parameter->getName()] = $this->serviceManagerService->get($parameterClass->getName());
                } catch (FactoryError $e) {
                    throw new ControllerError(sprintf(
                        'Class %s of parameter $%s for %s::%s not found!',
                        $parameterClass->getName(),
                        $parameter->getName(),
                        $reflectionMethod->getDeclaringClass()->getName(),
                        $reflectionMethod->getName()
                    ), 0, $e);
                }

                continue;
            }

            $parameters[$parameter->getName()] = $this->getParameterFromRequest($parameter, $attributeParameters);
        }

        return $parameters;
    }

    private function cleanParameters(ReflectionMethod $reflectionMethod, array $parameters): array
    {
        $newParameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (!isset($parameters[$parameter->getName()])) {
                continue;
            }

            $newParameters[] = $parameters[$parameter->getName()];
        }

        return $newParameters;
    }

    /**
     * @throws FactoryError
     *
     * @return array<array-key, array{service: AbstractActionAttributeService, attribute: AttributeInterface}>
     */
    private function getAttributes(ReflectionMethod $reflectionMethod): array
    {
        $attributesClasses = [];
        $attributes = $reflectionMethod->getAttributes(
            AttributeInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attributeClass */
            $attributeClass = $attribute->newInstance();
            /** @var AbstractActionAttributeService $attributeService */
            $attributeService = $this->serviceManagerService->get(
                $attributeClass->getAttributeServiceName(),
                AbstractActionAttributeService::class
            );

            $attributesClasses[] = [
                'service' => $attributeService,
                'attribute' => $attributeClass,
            ];
        }

        return $attributesClasses;
    }

    /**
     * @param array<array-key, array{service: AbstractActionAttributeService, attribute: AttributeInterface}> $attributes
     */
    private function preExecuteAttributes(array $attributes, array $parameters, array $reflectionParameters): array
    {
        /** @var array{service: AbstractActionAttributeService, attribute: AttributeInterface} $attribute */
        foreach ($attributes as $attribute) {
            $parameters = $attribute['service']->preExecute($attribute['attribute'], $parameters, $reflectionParameters);
        }

        return $parameters;
    }

    /**
     * @param array<array-key, array{service: AbstractActionAttributeService, attribute: AttributeInterface}> $attributes
     */
    private function postExecuteAttributes(array $attributes, ResponseInterface $response): void
    {
        /** @var array{service: AbstractActionAttributeService, attribute: AttributeInterface} $attribute */
        foreach ($attributes as $attribute) {
            $attribute['service']->postExecute($attribute['attribute'], $response);
        }
    }

    /**
     * @throws ControllerError
     * @throws JsonException
     */
    private function getParameterFromRequest(
        ReflectionParameter $parameter,
        array $attributeParameters
    ): array|bool|float|int|string|null {
        try {
            $value = $this->requestService->getRequestValue($parameter->getName());
        } catch (RequestError $e) {
            if ($parameter->isOptional()) {
                try {
                    return $parameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    throw new ControllerError($e->getMessage(), StatusCode::BAD_REQUEST, $e);
                }
            }

            if ($parameter->allowsNull()) {
                return null;
            }

            if (in_array($parameter->getName(), $attributeParameters)) {
                return null;
            }

            throw new ControllerError(sprintf(
                'Parameter %s is not in request!',
                $parameter->getName()
            ), 0, $e);
        }

        if ($value === null || $value === '') {
            if ($parameter->allowsNull()) {
                return null;
            }

            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw new ControllerError(sprintf(
                'Parameter %s doesnt allows null!',
                $parameter->getName()
            ));
        }

        /** @psalm-suppress UndefinedMethod */
        switch ($parameter->getType()?->getName()) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return $value === 'true' || ((int) $value);
            case 'string':
                return (string) $value;
            case 'array':
                if (!is_array($value)) {
                    return (array) JsonUtility::decode($value);
                }

                return $value;
            default:
                $declaringClass = $parameter->getDeclaringClass();

                throw new ControllerError(sprintf(
                    'Type %s of parameter %s for %s::%s is not allowed!',
                    (string) $parameter->getType(),
                    $parameter->getName(),
                    $declaringClass === null ? '' : $declaringClass->getName(),
                    $parameter->getDeclaringFunction()->getName()
                ));
        }
    }

    public function getControllerClassname(): string
    {
        $moduleName = $this->requestService->getModuleName();

        return
            'GibsonOS\\' .
            ($moduleName === 'core' ? '' : 'Module\\') .
            ucfirst($moduleName) . '\\Controller\\' .
            ucfirst($this->requestService->getTaskName()) . 'Controller'
        ;
    }
}
