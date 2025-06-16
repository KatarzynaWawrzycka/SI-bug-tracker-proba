<?php

namespace App\Service;

use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class UserService implements UserServiceInterface
{
    private UserRepository $userRepository;
    private PaginatorInterface $paginator;

    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
    }

    public function getPaginatedUsers(Request $request): PaginationInterface
    {
        $allUsers = $this->userRepository->findAll();

        $filtered = array_filter($allUsers, function ($user) {
            $roles = $user->getRoles();

            return 1 === count($roles) && in_array('ROLE_USER', $roles, true);
        });

        return $this->paginator->paginate(
            $filtered,
            $request->query->getInt('page', 1),
            10
        );
    }
}
