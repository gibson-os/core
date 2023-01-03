<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;

class UserAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws SelectError
     *
     * @return User[]
     */
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->userRepository->findByName($namePart);
    }

    /**
     * @throws SelectError
     */
    public function getById(string $id, array $parameters): AutoCompleteModelInterface
    {
        return $this->userRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.user.model.User';
    }

    public function getValueField(): string
    {
        return 'id';
    }

    public function getDisplayField(): string
    {
        return 'user';
    }
}
