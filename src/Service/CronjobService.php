<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Dto\Cronjob\Time;
use GibsonOS\Core\Dto\Cronjob\TimePart;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Repository\Cronjob\TimeRepository;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Psr\Log\LoggerInterface;
use ReflectionException;

class CronjobService
{
    public function __construct(
        private readonly ModelManager $modelManager,
        private readonly CronjobRepository $cronjobRepository,
        private readonly TimeRepository $timeRepository,
        private readonly CommandService $commandService,
        private readonly LoggerInterface $logger,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @param class-string $command
     *
     * @throws JsonException
     * @throws SaveError
     * @throws ReflectionException
     */
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
        array $options = [],
    ): void {
        try {
            $cronjob = $this->cronjobRepository->getByCommandAndUser($command, $user);
        } catch (SelectError) {
            $cronjob = (new Cronjob($this->modelWrapper))
                ->setCommand($command)
                ->setArguments(JsonUtility::encode($arguments))
                ->setOptions(JsonUtility::encode($options))
                ->setUser($user)
                ->setActive(true)
            ;
            $this->modelManager->saveWithoutChildren($cronjob);
        }

        if ($this->timeRepository->hasTimes($cronjob)) {
            return;
        }

        $times = $this->getCombinedTimes(
            $this->getTimesFromString($hours, 23),
            $this->getTimesFromString($minutes),
            $this->getTimesFromString($seconds),
            $this->getTimesFromString($daysOfMonth, 31, 1),
            $this->getTimesFromString($daysOfWeek, 6),
            $this->getTimesFromString($months, 12, 1),
            $this->getTimesFromString($years, 9999),
        );

        foreach ($times as $time) {
            $this->modelManager->save(
                (new Cronjob\Time($this->modelWrapper))
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
                    ->setToYear($time->getYear()->getTo()),
            );
        }
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ClientException
     * @throws RecordException
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
                empty($options) ? [] : (array) JsonUtility::decode($options),
            );

            $cronjob->setLastRun($dateTime);
            $this->modelManager->save($cronjob);

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
                (int) (count($fromAndToValue) === 1 ? $fromAndToValue[0] : $fromAndToValue[1]),
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
        array $years,
    ): array {
        $combinedTimes = [];
        $firstHour = reset($hours) ?: new TimePart(0, 23);
        $firstMinute = reset($minutes) ?: new TimePart(0, 59);
        $firstSecond = reset($seconds) ?: new TimePart(0, 59);
        $firstDayOfMonth = reset($daysOfMonth) ?: new TimePart(1, 31);
        $firstDayOfWeek = reset($daysOfWeek) ?: new TimePart(0, 6);
        $firstMonth = reset($months) ?: new TimePart(1, 12);
        $firstYear = reset($years) ?: new TimePart(1970, 9999);

        foreach ($hours as $hour) {
            $combinedTimes[] = new Time(
                $hour,
                $firstMinute,
                $firstSecond,
                $firstDayOfMonth,
                $firstDayOfWeek,
                $firstMonth,
                $firstYear,
            );
        }

        $counter = count($minutes);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $minutes[$i],
                $firstSecond,
                $firstDayOfMonth,
                $firstDayOfWeek,
                $firstMonth,
                $firstYear,
            );
        }

        $counter = count($seconds);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $firstMinute,
                $seconds[$i],
                $firstDayOfMonth,
                $firstDayOfWeek,
                $firstMonth,
                $firstYear,
            );
        }

        $counter = count($daysOfMonth);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $firstMinute,
                $firstSecond,
                $daysOfMonth[$i],
                $firstDayOfWeek,
                $firstMonth,
                $firstYear,
            );
        }

        $counter = count($daysOfWeek);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $firstMinute,
                $firstSecond,
                $firstDayOfMonth,
                $daysOfWeek[$i],
                $firstMonth,
                $firstYear,
            );
        }

        $counter = count($months);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $firstMinute,
                $firstSecond,
                $firstDayOfMonth,
                $firstDayOfWeek,
                $months[$i],
                $firstYear,
            );
        }

        $counter = count($years);

        for ($i = 1; $i < $counter; ++$i) {
            $combinedTimes[] = new Time(
                $firstHour,
                $firstMinute,
                $firstSecond,
                $firstDayOfMonth,
                $firstDayOfWeek,
                $firstMonth,
                $years[$i],
            );
        }

        return $combinedTimes;
    }
}
