<?php

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

interface UserServiceInterface
{
    public function getPaginatedUsers(Request $request): PaginationInterface;

    public function findOneByEmail(string $email): ?User;
}
