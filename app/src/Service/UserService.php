<?php

/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserRepository     $userRepository User repository
     * @param PaginatorInterface $paginator      Paginator interface
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * User paginated list.
     *
     * @param Request $request HTTP request
     *
     * @return PaginationInterface Paginated list
     */
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

    /**
     * Finds user by email.
     *
     * @param string $email Email
     *
     * @return User|null User
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
