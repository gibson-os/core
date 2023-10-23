<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Icon;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;

class TagRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Tag::class)]
        private readonly string $tagTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    public function deleteByIconId(int $iconId): bool
    {
        $deleteQuery = (new DeleteQuery($this->getTable($this->tagTableName)))
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
