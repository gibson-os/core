<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\Icon\TagRepository;
use Throwable;

class IconService
{
    private string $iconPath;

    #[GetSetting('custom_icon_path', 'core')]
    public function __construct(
        private TagRepository $tagRepository,
        private FileService $fileService,
        Setting $customIconPath
    ) {
        $this->iconPath = $customIconPath->getValue();
    }

    /**
     * @throws CreateError
     * @throws GetError
     * @throws SaveError
     * @throws SetError
     * @throws Throwable
     */
    public function save(Icon $icon, string $imageFilename, string $iconFilename = null, array $tags = []): bool
    {
        // @todo das Bild muss hochgeladen werden

        $connection = $icon->getMysqlTable()->connection;
        $connection->startTransaction();

        try {
            $icon->save();
            $this->fileService->copy(
                $imageFilename,
                $this->iconPath . 'original' . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.' . $icon->getOriginalType()
            );

            if ($iconFilename !== null) {
                $this->fileService->copy(
                    $iconFilename,
                    $this->iconPath . 'original' . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.ico'
                );
            }

            $this->tagRepository->deleteByIconId($icon->getId() ?? 0);

            foreach ($tags as $tag) {
                (new Icon\Tag())
                    ->setIcon($icon)
                    ->setTag($tag)
                    ->save();
            }
        } catch (Throwable $exception) {
            $connection->rollback();

            throw $exception;
        }

        $connection->commit();
    }
}
