<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\Response\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

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

    public function __construct(ServiceManagerService $serviceManagerService, RequestService $requestService)
    {
        $this->serviceManagerService = $serviceManagerService;
        $this->requestService = $requestService;
    }

    /**
     * @throws ControllerError
     */
    public function runAction(string $module, string $task, string $action): void
    {
        $controllerName = '\\GibsonOS\\' . ucfirst($module) . '\\' . ucfirst($task) . 'Controller';

        try {
            $controller = $this->serviceManagerService->get($controllerName);
            /** @psalm-suppress ArgumentTypeCoercion */
            $reflectionClass = new ReflectionClass($controllerName);
        } catch (ReflectionException | FactoryError $e) {
            throw new ControllerError(sprintf('Controller %s not found!', $controllerName), 0, $e);
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($action);
        } catch (ReflectionException $e) {
            throw new ControllerError(sprintf('Action %s::%s is not exists!', $controllerName, $action), 0, $e);
        }

        if (!$reflectionMethod->isPublic()) {
            throw new ControllerError(sprintf('Action %s::%s is not public!', $controllerName, $action));
        }

        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass instanceof ReflectionClass) {
                try {
                    $parameters[] = $this->serviceManagerService->get($parameterClass->getName());
                } catch (FactoryError $e) {
                    throw new ControllerError(sprintf(
                        'Class %s of parameter $%s not found!',
                        $parameterClass->getName(),
                        $parameter->getName()
                    ), 0, $e);
                }

                continue;
            }

            $parameters[] = $this->getParameterFromRequest($parameter);
        }

        /** @var ResponseInterface $response */
        $response = $controller->$action(...$parameters);

        $this->checkRequiredHeaders($response);

        foreach ($response->getHeaders() as $headerName => $headerValue) {
            header($headerName . ': ' . $headerValue);
        }

        echo $response->getBody();
    }

    /**
     * @throws ControllerError
     *
     * @return array|bool|float|int|string
     */
    private function getParameterFromRequest(ReflectionParameter $parameter)
    {
        try {
            $value = $this->requestService->getRequestValue($parameter->getName());
        } catch (RequestError $e) {
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
                return (array) $value;
            default:
                throw new ControllerError(sprintf(
                    'Type %s of parameter %s is not allowed!',
                    (string) $parameter->getType(),
                    $parameter->getName()
                ));
        }
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
}
