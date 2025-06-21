<?php

/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * User paginated list.
     *
     * @param Request $request HTTP request
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedUsers(Request $request): PaginationInterface;

    /**
     * Finds user by email.
     *
     * @param string $email Email
     *
     * @return User|null User
     */
    public function findOneByEmail(string $email): ?User;
}
