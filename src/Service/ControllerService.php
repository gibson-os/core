<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;
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
        } catch (ReflectionException | FactoryError $e) {
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
            $this->evaluateAttributes($reflectionMethod);

            $parameters = $this->getParameters($reflectionMethod);
            /** @var ResponseInterface $response */
            $response = $controller->$action(...$parameters);

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
     * @throws ControllerError
     */
    private function getParameters(ReflectionMethod $reflectionMethod): array
    {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass instanceof ReflectionClass) {
                try {
                    $parameters[] = $this->serviceManagerService->get($parameterClass->getName());
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

            $parameters[] = $this->getParameterFromRequest($parameter);
        }

        return $parameters;
    }

    /**
     * @throws FactoryError
     * @throws ControllerError
     */
    private function evaluateAttributes(ReflectionMethod $reflectionMethod): void
    {
        $attributes = $reflectionMethod->getAttributes(
            AttributeInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attributeClass */
            $attributeClass = $attribute->newInstance();
            /** @var AttributeServiceInterface $attributeService */
            $attributeService = $this->serviceManagerService->get($attributeClass->getAttributeServiceName());

            if ($attributeService->evaluateAttribute($attributeClass) === false) {
                throw new ControllerError(sprintf('Attribute %d is not valid!', $attribute->getName()));
            }
        }
    }

    /**
     * @throws ControllerError
     * @throws JsonException
     */
    private function getParameterFromRequest(ReflectionParameter $parameter): array|bool|float|int|string|null
    {
        try {
            $value = $this->requestService->getRequestValue($parameter->getName());
        } catch (RequestError $e) {
            if ($parameter->isOptional()) {
                try {
                    return $parameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    throw new ControllerError($e->getMessage(), 0, $e);
                }
            }

            if ($parameter->allowsNull()) {
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
                return $value === 'true' || (bool)((int)$value);
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
