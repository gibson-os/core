<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;

class LocationAutoComplete implements AutoCompleteInterface
{
    public function __construct(private LocationRepository $locationRepository)
    {
    }

    /**
     * @throws DateTimeError
     */
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
}
