<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use Exception;
use Generator;
use GibsonOS\Core\Attribute\AlwaysAjaxResponse;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\IconRepository;
use GibsonOS\Core\Service\IconService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Icon\TagStore;
use GibsonOS\Core\Store\IconStore;
use Throwable;

class IconController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(IconStore $iconStore, TagStore $tagStore, array $tags = []): AjaxResponse
    {
        $iconStore->setTags($tags);

        /** @var Generator $icons */
        $icons = $iconStore->getList();

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => [...$icons],
            'tags' => $tagStore->getList(),
        ]);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws CreateError
     * @throws GetError
     * @throws SetError
     * @throws Throwable
     *
     * @return AjaxResponse
     */
    #[CheckPermission(Permission::WRITE)]
    #[AlwaysAjaxResponse]
    public function save(
        IconRepository $iconRepository,
        ImageService $imageService,
        IconService $iconService,
        string $name,
        string $tags,
        array $icon,
        array $iconIco = null,
        int $id = null
    ): AjaxResponse {
        if ($id !== null) {
            $iconModel = $iconRepository->getById($id);
        } else {
            $iconModel = (new Icon())
                ->setName($name)
                ->setOriginalType($imageService->getImageTypeByMimeType($icon['type']))
            ;
        }

        $iconService->save(
            $iconModel,
            $icon['tmp_name'],
            $iconIco === null ? null : $iconIco['tmp_name'],
            explode(',', $tags)
        );

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::DELETE)]
    public function delete(IconRepository $iconRepository, array $ids, IconService $iconService): AjaxResponse
    {
        $iconRepository->startTransaction();

        try {
            foreach ($iconRepository->findByIds($ids) as $icon) {
                $iconService->delete($icon);
            }
        } catch (Exception) {
            $iconRepository->rollback();

            return $this->returnFailure('Icons not deleted!');
        }

        $iconRepository->commit();

        return $this->returnSuccess();
    }
}
