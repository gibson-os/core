<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\WeatherService;
use Psr\Log\LoggerInterface;

class WeatherCommand extends AbstractCommand
{
    private WeatherService $weatherService;

    public function __construct(LoggerInterface $logger, WeatherService $weatherService)
    {
        parent::__construct($logger);
        $this->weatherService = $weatherService;
    }

    protected function run(): int
    {
        $this->weatherService->load(
            (new Location())
                ->setId(1)
                ->setName('Düsseldorf')
                ->setTimezone('Europe/Berlin')
                ->setLatitude(51.2217)
                ->setLongitude(6.7762)
        );

        return 1;
    }
}
