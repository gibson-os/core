<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\Desktop\ItemRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class DesktopController extends AbstractController
{
    public const DESKTOP_KEY = 'desktop';

    public const APPS_KEY = 'apps';

    public const TOOLS_KEY = 'tools';

    /**
     * @throws JsonException
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function getIndex(
        ItemRepository $itemRepository,
        #[GetSetting(self::APPS_KEY)] ?Setting $apps,
        #[GetSetting(self::TOOLS_KEY)] ?Setting $tools,
        User $permissionUser,
    ): AjaxResponse {
        return $this->returnSuccess([
            self::DESKTOP_KEY => $itemRepository->getByUser($permissionUser),
            self::APPS_KEY => JsonUtility::decode($apps?->getValue() ?: '[]'),
            self::TOOLS_KEY => JsonUtility::decode($tools?->getValue() ?: '[]'),
        ]);
    }

    /**
     * @param Item[] $items
     *
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function postSave(
        ModelManager $modelManager,
        ItemRepository $itemRepository,
        #[GetMappedModels(Item::class)] array $items,
        User $permissionUser
    ): AjaxResponse {
        $position = 0;
        $itemIds = [];

        foreach ($items as $item) {
            $modelManager->saveWithoutChildren(
                $item
                    ->setUser($permissionUser)
                    ->setPosition($position++)
            );
            $itemIds[] = $item->getId();
        }

        $itemRepository->deleteByIdsNot($permissionUser, $itemIds);

        return $this->returnSuccess($items);
    }

    /**
     * @param non-empty-array<Item> $items
     *
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function postAdd(
        ModelManager $modelManager,
        ItemRepository $itemRepository,
        #[GetMappedModels(Item::class)] array $items,
        User $permissionUser
    ): AjaxResponse {
        $nextPosition = -1;

        foreach ($items as $item) {
            if ($item->getPosition() >= 0) {
                break;
            }

            if ($nextPosition === -1) {
                try {
                    $nextPosition = $itemRepository->getLastPosition($permissionUser)->getPosition() + 1;
                } catch (SelectError) {
                    $nextPosition = 0;
                }
            }

            $item->setPosition($nextPosition++);
        }

        $itemRepository->updatePosition(
            $permissionUser,
            min(array_map(fn (Item $item): int => $item->getPosition(), $items)),
            count($items),
        );

        foreach ($items as $item) {
            $modelManager->saveWithoutChildren($item->setUser($permissionUser));
        }

        return $this->returnSuccess($items);
    }
}
