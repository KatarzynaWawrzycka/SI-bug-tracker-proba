<?php

/**
 * Bug service interface.
 */

namespace App\Service;

use App\Dto\BugListInputFiltersDto;
use App\Entity\Bug;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface BugServiceInterface.
 */
interface BugServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                    $page    Page number
     * @param User|null              $user    User
     * @param BugListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?User $user, BugListInputFiltersDto $filters): PaginationInterface;

    /**
     * Find bug by id.
     *
     * @param int $id Id
     *
     * @return Bug|null Bug
     */
    public function findOneById(int $id): ?Bug;

    /**
     * Save entity.
     *
     * @param Bug $bug Bug entity
     */
    public function save(Bug $bug): void;

    /**
     * Delete entity.
     *
     * @param Bug $bug Bug entity
     */
    public function delete(Bug $bug): void;
}
