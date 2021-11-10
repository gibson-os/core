<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError as FileDeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError;
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
    public function save(Icon $icon, string $imageFilename, string $iconFilename = null, array $tags = []): void
    {
        $database = $icon->getDatabase();
        $database->startTransaction();

        try {
            $icon->save();
            $this->fileService->copy(
                $imageFilename,
                $this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.' . $icon->getOriginalType()
            );

            if (!empty($iconFilename)) {
                $this->fileService->copy(
                    $iconFilename,
                    $this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.ico'
                );
            }

            $this->tagRepository->deleteByIconId($icon->getId() ?? 0);

            foreach ($tags as $tag) {
                (new Icon\Tag())
                    ->setIcon($icon)
                    ->setTag(trim($tag))
                    ->save();
            }
        } catch (Throwable $exception) {
            $database->rollback();

            throw $exception;
        }

        $database->commit();
    }

    /**N
     * @throws GetError
     * @throws FileDeleteError
     * @throws FileNotFound
     * @throws DeleteError
     */
    public function delete(Icon $icon): void
    {
        $icon->delete();
        $this->fileService->delete($this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.' . $icon->getOriginalType());

        if ($this->fileService->exists($this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.ico')) {
            $this->fileService->delete($this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $icon->getId() . '.ico');
        }
    }
}
