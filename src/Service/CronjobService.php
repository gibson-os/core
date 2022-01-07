<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Dto\Cronjob\Time;
use GibsonOS\Core\Dto\Cronjob\TimePart;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Psr\Log\LoggerInterface;

class CronjobService
{
    public function __construct(
        private CronjobRepository $cronjobRepository,
        private CommandService $commandService,
        private LoggerInterface $logger
    ) {
    }

    public function add(
        string $command,
        string $user,
        string $hours,
        string $minutes,
        string $seconds,
        string $daysOfMonth,
        string $daysOfWeek,
        string $months,
        string $years,
        array $arguments = [],
        array $options = []
    ): void {
        $cronjob = (new Cronjob())
            ->setCommand($command)
            ->setArguments(JsonUtility::encode($arguments))
            ->setOptions(JsonUtility::encode($options))
            ->setUser($user)
            ->setActive(true)
        ;
        $cronjob->save();

        $times = $this->getCombinedTimes(
            $this->getTimesFromString($hours),
            $this->getTimesFromString($minutes),
            $this->getTimesFromString($seconds),
            $this->getTimesFromString($daysOfMonth, 31, 1),
            $this->getTimesFromString($daysOfWeek, 6),
            $this->getTimesFromString($months, 12, 1),
            $this->getTimesFromString($years, 9999)
        );

        foreach ($times as $time) {
            (new Cronjob\Time())
                ->setCronjob($cronjob)
                ->setFromHour($time->getHour()->getFrom())
                ->setToHour($time->getHour()->getTo())
                ->setFromMinute($time->getMinute()->getFrom())
                ->setToMinute($time->getMinute()->getTo())
                ->setFromSecond($time->getSecond()->getFrom())
                ->setToSecond($time->getSecond()->getTo())
                ->setFromDayOfMonth($time->getDayOfMonth()->getFrom())
                ->setToDayOfMonth($time->getDayOfMonth()->getTo())
                ->setFromDayOfWeek($time->getDayOfWeek()->getFrom())
                ->setToDayOfWeek($time->getDayOfWeek()->getTo())
                ->setFromMonth($time->getMonth()->getFrom())
                ->setToMonth($time->getMonth()->getTo())
                ->setFromYear($time->getYear()->getFrom())
                ->setToYear($time->getYear()->getTo())
                ->save()
            ;
        }
    }

    /**
     * @throws SaveError
     * @throws JsonException
     */
    public function run(string $user): void
    {
        $dateTime = new DateTime();

        foreach ($this->cronjobRepository->getRunnableByUser($dateTime, $user) as $cronjob) {
            $this->logger->info(sprintf('Run cronjob %s', $cronjob->getCommand()));

            $arguments = $cronjob->getArguments();
            $options = $cronjob->getOptions();
            $this->commandService->executeAsync(
                $cronjob->getCommand(),
                empty($arguments) ? [] : (array) JsonUtility::decode($arguments),
                empty($options) ? [] : (array) JsonUtility::decode($options)
            );

            $cronjob->setLastRun($dateTime);
            $cronjob->save();

            $dateTime = new DateTime();
        }
    }

    /**
     * @return TimePart[]
     */
    private function getTimesFromString(string $string, int $defaultTo = 59, int $defaultFrom = 0): array
    {
        if ($string === '*') {
            return [new TimePart($defaultFrom, $defaultTo)];
        }

        $times = [];

        foreach (explode(',', $string) as $part) {
            $fromAndToValue = explode('-', $part);
            $times[] = new TimePart(
                (int) $fromAndToValue[0],
                (int) (count($fromAndToValue) === 1 ? $fromAndToValue[0] : $fromAndToValue[1])
            );
        }

        return $times;
    }

    /**
     * @param TimePart[] $hours
     * @param TimePart[] $minutes
     * @param TimePart[] $seconds
     * @param TimePart[] $daysOfMonth
     * @param TimePart[] $daysOfWeek
     * @param TimePart[] $months
     * @param TimePart[] $years
     *
     * @return Time[]
     */
    private function getCombinedTimes(
        array $hours,
        array $minutes,
        array $seconds,
        array $daysOfMonth,
        array $daysOfWeek,
        array $months,
        array $years
    ): array {
        $combinedTimes = [];

        foreach ($hours as $hour) {
            $combinedTimes[] = new Time(
                $hour,
                reset($minutes),
                reset($seconds),
                reset($daysOfMonth),
                reset($daysOfWeek),
                reset($months),
                reset($years),
            );
        }

        for ($i = 1; $i < count($minutes); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                $minutes[$i],
                reset($seconds),
                reset($daysOfMonth),
                reset($daysOfWeek),
                reset($months),
                reset($years),
            );
        }

        for ($i = 1; $i < count($seconds); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                reset($minutes),
                $seconds[$i],
                reset($daysOfMonth),
                reset($daysOfWeek),
                reset($months),
                reset($years),
            );
        }

        for ($i = 1; $i < count($daysOfMonth); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                reset($minutes),
                reset($seconds),
                $daysOfMonth[$i],
                reset($daysOfWeek),
                reset($months),
                reset($years),
            );
        }

        for ($i = 1; $i < count($daysOfWeek); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                reset($minutes),
                reset($seconds),
                reset($daysOfMonth),
                $daysOfWeek[$i],
                reset($months),
                reset($years),
            );
        }

        for ($i = 1; $i < count($months); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                reset($minutes),
                reset($seconds),
                reset($daysOfMonth),
                reset($daysOfWeek),
                $months[$i],
                reset($years),
            );
        }

        for ($i = 1; $i < count($years); ++$i) {
            $combinedTimes[] = new Time(
                reset($hours),
                reset($minutes),
                reset($seconds),
                reset($daysOfMonth),
                reset($daysOfWeek),
                reset($months),
                $years[$i],
            );
        }

        return $combinedTimes;
    }
}
