<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Utility\JsonUtility;
use Psr\Log\LoggerInterface;

/**
 * @description Migrate desktop setting to item table
 */
class TempMigrateDesktop extends AbstractCommand
{
    public function __construct(
        private readonly ModelManager $modelManager,
        private readonly SettingRepository $settingRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws \JsonException
     */
    protected function run(): int
    {
        $position = 0;

        foreach ($this->settingRepository->getDesktops() as $desktopSetting) {
            $desktopItems = JsonUtility::decode($desktopSetting->getValue());

            foreach ($desktopItems as $desktopItem) {
                $this->modelManager->saveWithoutChildren(
                    (new Item())
                        ->setText($desktopItem['text'])
                        ->setIcon($desktopItem['icon'])
                        ->setPosition($position++)
                        ->setUserId($desktopSetting->getUserId() ?? 0)
                        ->setModule($desktopItem['module'])
                        ->setTask($desktopItem['task'])
                        ->setAction($desktopItem['action'])
                        ->setParameters($desktopItem['params'] ?? [])
                );
            }
        }

        return self::SUCCESS;
    }
}
