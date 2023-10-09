<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Icon;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;

class TagRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTable(Tag::class)]
        private readonly Table $tagTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    public function deleteByIconId(int $iconId): bool
    {
        $deleteQuery = (new DeleteQuery($this->tagTable))
            ->addWhere(new Where('`icon_id`=?', [$iconId]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
