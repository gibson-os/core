<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use Override;

class LocationAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly LocationRepository $locationRepository)
    {
    }

    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->locationRepository->findByName($namePart, (bool) ($parameters['onlyActive'] ?? false));
    }

    /**
     * @throws SelectError
     */
    #[Override]
    public function getById(string $id, array $parameters): Location
    {
        return $this->locationRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.weather.model.Location';
    }

    #[Override]
    public function getValueField(): string
    {
        return 'id';
    }

    #[Override]
    public function getDisplayField(): string
    {
        return 'name';
    }
}
