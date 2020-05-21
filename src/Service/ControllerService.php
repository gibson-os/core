<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\Response\ExceptionResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class ControllerService
{
    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    /**
     * @var RequestService
     */
    private $requestService;

    /**
     * @var StatusCode
     */
    private $statusCode;

    public function __construct(
        ServiceManagerService $serviceManagerService,
        RequestService $requestService,
        StatusCode $statusCode
    ) {
        $this->serviceManagerService = $serviceManagerService;
        $this->requestService = $requestService;
        $this->statusCode = $statusCode;
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
                $this->requestService
            ));

            return;
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($action);
        } catch (ReflectionException $e) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s not exists!', $controllerName, $action), 404, $e),
                $this->requestService
            ));

            return;
        }

        if (!$reflectionMethod->isPublic()) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s is not public!', $controllerName, $action), 405),
                $this->requestService
            ));

            return;
        }

        try {
            $parameters = $this->getParameters($reflectionMethod);
            /** @var ResponseInterface $response */
            $response = $controller->$action(...$parameters);
            $this->checkRequiredHeaders($response);
        } catch (Throwable $e) {
            $response = new ExceptionResponse($e, $this->requestService);
        }

        $this->outputResponse($response);
    }

    private function outputResponse(ResponseInterface $response): void
    {
        header($this->statusCode->getStatusHeader($response->getCode()));

        foreach ($response->getHeaders() as $headerName => $headerValue) {
            header($headerName . ': ' . $headerValue);
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
                        $reflectionMethod->getName(),
                        $reflectionMethod->getDeclaringClass()->getName()
                    ), 0, $e);
                }

                continue;
            }

            $parameters[] = $this->getParameterFromRequest($parameter);
        }

        return $parameters;
    }

    /**
     * @throws ControllerError
     *
     * @return array|bool|float|int|string|null
     */
    private function getParameterFromRequest(ReflectionParameter $parameter)
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

        switch ($parameter->getType()) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return $value === 'true' ? true : (bool) ((int) $value);
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
