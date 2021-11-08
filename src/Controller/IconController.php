<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\IconRepository;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Icon\TagStore;
use GibsonOS\Core\Store\IconStore;

class IconController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(IconStore $iconStore, TagStore $tagStore, array $tags = []): AjaxResponse
    {
        $iconStore->setTags($tags);

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => [...$iconStore->getList()],
            'tags' => [...$tagStore->getList()],
        ]);
    }

    /**
     * @throws SelectError
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function save(
        IconRepository $iconRepository,
        ImageService $imageService,
        string $name,
        array $tags,
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

        // @todo das Bild muss hochgeladen werden
        $iconModel->save();

        foreach ($tags as $tag) {
            (new Icon\Tag())
                ->setIcon($iconModel)
                ->setTag($tag)
                ->save()
            ;
        }

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::DELETE)]
    public function delete(IconRepository $iconRepository, array $ids): AjaxResponse
    {
        if ($iconRepository->deleteByIds($ids)) {
            return $this->returnSuccess();
        }

        return $this->returnFailure('Icons not deleted!');
    }
}
