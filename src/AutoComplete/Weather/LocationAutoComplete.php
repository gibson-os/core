<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\AutoCompleteException;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;

class LocationAutoComplete implements AutoCompleteInterface
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @throws DateTimeError
     */
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->locationRepository->findByName($namePart, (bool) ($parameters['onlyActive'] ?? false));
    }

    /**
     * @param int $id
     *
     * @throws SelectError
     */
    public function getById($id, array $parameters): ModelInterface
    {
        return $this->locationRepository->getById($id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.weather.model.Location';
    }

    /**
     * @throws AutoCompleteException
     */
    public function getIdFromModel(ModelInterface $model): int
    {
        if (!$model instanceof Location) {
            throw new AutoCompleteException(sprintf('Model is not instance of %s', Location::class));
        }

        return $model->getId() ?? 0;
    }
}
