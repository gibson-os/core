<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Model\SmartAttribute;
use JsonException;
use MDO\Exception\ClientException;
use ReflectionException;

class SmartAttributeRepository extends AbstractRepository
{
    /**
     * @throws JsonException
     * @throws ClientException
     * @throws ReflectionException
     *
     * @return SmartAttribute[]
     */
    public function getAll(): array
    {
        return $this->fetchAll('', [], SmartAttribute::class);
    }
}
