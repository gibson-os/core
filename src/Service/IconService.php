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
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\Icon\TagRepository;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Client;
use Throwable;

class IconService
{
    private readonly string $iconPath;

    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly FileService $fileService,
        private readonly ModelManager $modelManager,
        private readonly ModelWrapper $modelWrapper,
        private readonly Client $client,
        #[GetSetting('custom_icon_path', 'core')]
        Setting $customIconPath,
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
    public function save(Icon $icon, string $imageFilename, ?string $iconFilename = null, array $tags = []): void
    {
        $this->client->startTransaction();

        try {
            $this->modelManager->save($icon);
            $id = $icon->getId() ?? 0;
            $this->fileService->copy(
                $imageFilename,
                $this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $id . '.' . $icon->getOriginalType(),
            );

            if ($iconFilename !== null && $iconFilename !== '') {
                $this->fileService->copy(
                    $iconFilename,
                    $this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $id . '.ico',
                );
            }

            $this->tagRepository->deleteByIconId($id);

            foreach ($tags as $tag) {
                $this->modelManager->save(
                    (new Icon\Tag($this->modelWrapper))
                        ->setIcon($icon)
                        ->setTag(trim($tag)),
                );
            }
        } catch (Throwable $exception) {
            $this->client->rollback();

            throw $exception;
        }

        $this->client->commit();
    }

    /**N
     * @throws GetError
     * @throws FileDeleteError
     * @throws FileNotFound
     * @throws DeleteError
     * @throws JsonException
     */
    public function delete(Icon $icon): void
    {
        $this->modelManager->delete($icon);
        $id = $icon->getId() ?? 0;
        $this->fileService->delete($this->iconPath . DIRECTORY_SEPARATOR . 'icon' . $id . '.' . $icon->getOriginalType());
        $iconFilename = sprintf(
            '%s%sicon%s.ico',
            $this->iconPath,
            DIRECTORY_SEPARATOR,
            $id,
        );

        if ($this->fileService->exists($iconFilename)) {
            $this->fileService->delete($iconFilename);
        }
    }
}
