<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use ReflectionException;

class UserAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return User[]
     */
    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->userRepository->findByName($namePart);
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    #[Override]
    public function getById(string $id, array $parameters): User
    {
        return $this->userRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.user.model.User';
    }

    #[Override]
    public function getValueField(): string
    {
        return 'id';
    }

    #[Override]
    public function getDisplayField(): string
    {
        return 'user';
    }
}
