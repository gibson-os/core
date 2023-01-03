<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;

class LocationAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly LocationRepository $locationRepository)
    {
    }

    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->locationRepository->findByName($namePart, (bool) ($parameters['onlyActive'] ?? false));
    }

    /**
     * @throws SelectError
     */
    public function getById(string $id, array $parameters): Location
    {
        return $this->locationRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.weather.model.Location';
    }

    public function getValueField(): string
    {
        return 'id';
    }

    public function getDisplayField(): string
    {
        return 'name';
    }
}
