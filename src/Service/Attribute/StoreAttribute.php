<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Core\Utility\JsonUtility;
use ReflectionException;
use ReflectionParameter;

class StoreAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        private readonly ServiceManager $serviceManager,
        private readonly RequestService $requestService,
    ) {
    }

    /**
     * @throws MapperException
     * @throws FactoryError
     * @throws ReflectionException
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter,
    ): AbstractStore {
        if (!$attribute instanceof GetStore) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetStore::class,
            ));
        }

        $storeClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);

        if (!is_subclass_of($storeClassName, AbstractStore::class)) {
            throw new MapperException(sprintf(
                'Store "%s" is no instance of "%s"!',
                $storeClassName,
                AbstractStore::class,
            ));
        }

        /** @var AbstractStore $store */
        $store = $this->serviceManager->get($storeClassName);

        try {
            $store->setSortByExt(JsonUtility::decode($this->requestService->getRequestValue($attribute->getSortParameter())));
        } catch (RequestError) {
            // do nothing
        }

        try {
            $store->setFilters(JsonUtility::decode($this->requestService->getRequestValue($attribute->getFiltersParameter())));
        } catch (RequestError) {
            // do nothing
        }

        try {
            $start = (int) $this->requestService->getRequestValue($attribute->getStartParameter());
        } catch (RequestError) {
            $start = 0;
        }

        try {
            $limit = (int) $this->requestService->getRequestValue($attribute->getLimitParameter());
        } catch (RequestError) {
            $limit = 0;
        }

        return $store->setLimit($limit, $start);
    }
}
