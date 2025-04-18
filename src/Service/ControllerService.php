<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AlwaysAjaxResponse;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Dto\Attribute;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Core\Service\Attribute\ParameterAttributeInterface;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ExceptionResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use InvalidArgumentException;
use JsonException;
use OutOfBoundsException;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

readonly class ControllerService
{
    private readonly Setting $chromecastReceiverAppId;

    /**
     * @throws SelectError
     */
    public function __construct(
        private ServiceManager $serviceManagerService,
        private RequestService $requestService,
        private TwigService $twigService,
        private EnvService $envService,
        private AttributeService $attributeService,
        private ObjectMapperAttribute $objectMapperAttribute,
        private ReflectionManager $reflectionManager,
        private MiddlewareService $middlewareService,
        private ModelManager $modelManager,
        private ModelWrapper $modelWrapper,
        private TracerService $tracerService,
        ModuleRepository $moduleRepository,
        #[GetSetting('chromecastReceiverAppId', 'core')]
        ?Setting $chromecastReceiverAppId = null,
    ) {
        $this->chromecastReceiverAppId = $chromecastReceiverAppId
            ?? (new Setting($this->modelWrapper))
                ->setModule($moduleRepository->getByName('core'))
                ->setKey('chromecastReceiverAppId')
        ;
    }

    public function runAction(): void
    {
        $controllerName = $this->getControllerClassname();
        $action = mb_strtolower($this->requestService->getMethod()) . ucfirst($this->requestService->getActionName());

        $this->tracerService
            ->setTransactionName(sprintf('%s::%s', $controllerName, $action))
            ->setCustomParameters($this->requestService->getRequestValues(), TracePrefix::REQUEST_VALUE)
            ->setCustomParameter('controller', true)
            ->setCustomParameter('method', $this->requestService->getMethod())
        ;

        try {
            $controller = $this->serviceManagerService->get($controllerName);
            /** @psalm-suppress ArgumentTypeCoercion */
            $reflectionClass = $this->reflectionManager->getReflectionClass($controllerName);
        } catch (ReflectionException|FactoryError $e) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Controller %s not found!', $controllerName), 404, $e),
                $this->requestService,
                $this->twigService,
            ));

            return;
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($action);
        } catch (ReflectionException $e) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s not exists!', $controllerName, $action), 404, $e),
                $this->requestService,
                $this->twigService,
            ));

            return;
        }

        if (!$reflectionMethod->isPublic()) {
            $this->outputResponse(new ExceptionResponse(
                new ControllerError(sprintf('Action %s::%s is not public!', $controllerName, $action), 405),
                $this->requestService,
                $this->twigService,
            ));

            return;
        }

        try {
            $attributes = $this->attributeService->getAttributes($reflectionMethod);
            $parameters = $this->getParameters($reflectionMethod, $attributes);
            $parameters = $this->preExecuteAttributes($attributes, $parameters, $reflectionMethod->getParameters());
            $parameters = $this->cleanParameters($reflectionMethod, $parameters);

            /** @var ResponseInterface $response */
            $response = $controller->$action(...$parameters);
            $this->postExecuteAttributes($attributes, $response);

            $alwaysAjaxAttributes = $this->attributeService->getAttributesByClassName(
                $reflectionMethod,
                AlwaysAjaxResponse::class,
            );

            if ($alwaysAjaxAttributes === []) {
                try {
                    $this->checkRequiredHeaders($response);
                } catch (ControllerError $e) {
                    error_log($e->getMessage());

                    if (!$response instanceof AjaxResponse) {
                        throw $e;
                    }

                    $response = $this->renderTemplate();
                }
            }
        } catch (Throwable $e) {
            $response = new ExceptionResponse($e, $this->requestService, $this->twigService);
        }

        $this->outputResponse($response);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws MiddlewareException
     * @throws SaveError
     * @throws WebException
     * @throws ReflectionException
     */
    private function renderTemplate(): TwigResponse
    {
        if ($this->chromecastReceiverAppId->getId() === null) {
            try {
                $response = $this->middlewareService->send('chromecast', 'receiverAppId');
                $this->chromecastReceiverAppId
                    ->setValue(JsonUtility::decode($response->getBody()->getContent())['data'])
                ;
            } catch (InvalidArgumentException) {
                $this->chromecastReceiverAppId->setValue('');
            }

            $this->modelManager->saveWithoutChildren($this->chromecastReceiverAppId);
        }

        $now = time();
        $serverDate = date_sun_info(
            $now,
            $this->envService->getFloat('DATE_LATITUDE'),
            $this->envService->getFloat('DATE_LONGITUDE'),
        );
        $serverDate['now'] = $now;
        $context = [
            'baseDir' => preg_replace('|^(.*/).+?$|', '$1', $_SERVER['SCRIPT_NAME'] ?? ''),
            'domain' => strtolower($_SERVER['REQUEST_SCHEME'] ?? '') . '://' . ($_SERVER['HTTP_HOST'] ?? ''),
            'serverDate' => $serverDate,
            'request' => $this->requestService,
            'session' => $this->serviceManagerService->get(SessionService::class),
            'chromecastReceiverAppId' => $this->chromecastReceiverAppId->getValue(),
        ];

        return (new TwigResponse($this->twigService, '@core/base.html.twig'))
            ->setVariables($context)
        ;
    }

    private function outputResponse(ResponseInterface $response): void
    {
        try {
            header($response->getCode()->getStatusHeader());
        } catch (OutOfBoundsException $exception) {
            $this->outputResponse(new ExceptionResponse(
                $exception,
                $this->requestService,
                $this->twigService,
            ));

            return;
        }

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            if (!is_array($headerValues)) {
                $headerValues = [$headerValues];
            }

            foreach ($headerValues as $index => $headerValue) {
                header($headerName . ': ' . $headerValue, $index === 0);
            }
        }

        $this->tracerService->setCustomParameter('statusCode', $response->getCode()->value);

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
                    $requiredValue,
                ));
            }
        }
    }

    /**
     * @param Attribute[] $attributes
     *
     * @throws ControllerError
     * @throws FactoryError
     * @throws JsonException
     * @throws ReflectionException
     * @throws MapperException
     */
    private function getParameters(ReflectionMethod $reflectionMethod, array $attributes): array
    {
        $parameters = [];
        $attributeParameters = [];

        foreach ($attributes as $attribute) {
            $attributeService = $attribute->getService();

            if (!$attributeService instanceof AbstractActionAttributeService) {
                continue;
            }

            $attributeParameters = array_merge(
                $attributeParameters,
                $attributeService->usedParameters($attribute->getAttribute()),
            );
        }

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $name = $reflectionParameter->getName();

            if (array_key_exists($name, $parameters)) {
                continue;
            }

            $attributes = $this->attributeService->getAttributes($reflectionParameter);

            if ($attributes !== []) {
                foreach ($attributes as $attribute) {
                    /** @var ParameterAttributeInterface $attributeService */
                    $attributeService = $attribute->getService();
                    $parameters[$name] = $attributeService->replace(
                        $attribute->getAttribute(),
                        $parameters,
                        $reflectionParameter,
                    );
                }

                continue;
            }

            $parameterType = $reflectionParameter->getType();

            if (
                $parameterType instanceof ReflectionNamedType
                && !$parameterType->isBuiltin()
            ) {
                $typeName = $parameterType->getName();

                if (enum_exists($typeName)) {
                    $value = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);

                    try {
                        $parameters[$name] = $value === null || is_object($value) || is_array($value)
                            ? null
                            : constant(sprintf('%s::%s', $typeName, (string) $value))
                        ;
                    } catch (Throwable) {
                        $enumReflection = $this->reflectionManager->getReflectionEnum($typeName);

                        $parameters[$name] = $typeName::from(match ((string) $enumReflection->getBackingType()) {
                            'string' => (string) $value,
                            'int' => (int) $value,
                            'float' => (float) $value,
                        });
                    }

                    continue;
                }

                if (!class_exists($typeName)) {
                    throw new ControllerError(sprintf(
                        'Class %s of parameter $%s for %s::%s not found!',
                        $typeName,
                        $name,
                        $reflectionMethod->getDeclaringClass()->getName(),
                        $reflectionMethod->getName(),
                    ));
                }

                try {
                    $parameters[$name] = $this->serviceManagerService->get($typeName);
                } catch (FactoryError $exception) {
                    throw new ControllerError(sprintf(
                        'Class %s of parameter $%s for %s::%s not found!',
                        $typeName,
                        $name,
                        $reflectionMethod->getDeclaringClass()->getName(),
                        $reflectionMethod->getName(),
                    ), 0, $exception);
                }

                continue;
            }

            $parameters[$name] = in_array($name, $attributeParameters)
                ? null
                : $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter)
            ;
        }

        return $parameters;
    }

    private function cleanParameters(ReflectionMethod $reflectionMethod, array $parameters): array
    {
        $newParameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (!array_key_exists($parameter->getName(), $parameters)) {
                continue;
            }

            $newParameters[] = $parameters[$parameter->getName()];
        }

        return $newParameters;
    }

    /**
     * @param Attribute[] $attributes
     */
    private function preExecuteAttributes(array $attributes, array $parameters, array $reflectionParameters): array
    {
        foreach ($attributes as $attribute) {
            $attributeService = $attribute->getService();

            if (!$attributeService instanceof AbstractActionAttributeService) {
                continue;
            }

            $parameters = $attributeService->preExecute($attribute->getAttribute(), $parameters, $reflectionParameters);
        }

        return $parameters;
    }

    /**
     * @param Attribute[] $attributes
     */
    private function postExecuteAttributes(array $attributes, ResponseInterface $response): void
    {
        foreach ($attributes as $attribute) {
            $attributeService = $attribute->getService();

            if (!$attributeService instanceof AbstractActionAttributeService) {
                continue;
            }

            $attributeService->postExecute($attribute->getAttribute(), $response);
        }
    }

    /**
     * @return class-string
     */
    public function getControllerClassname(): string
    {
        $moduleName = $this->requestService->getModuleName();

        /** @var class-string $className */
        $className =
            'GibsonOS\\' .
            ($moduleName === 'core' ? '' : 'Module\\') .
            ucfirst($moduleName) . '\\Controller\\' .
            ucfirst($this->requestService->getTaskName()) . 'Controller'
        ;

        return $className;
    }
}
