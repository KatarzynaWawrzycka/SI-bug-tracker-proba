<?php

/**
 * Comment service interface.
 */

namespace App\Service;

use App\Entity\Bug;
use App\Entity\Comment;

/**
 * Interface CommentServiceInterface.
 */
interface CommentServiceInterface
{
    /**
     * Find comments by bug.
     *
     * @param Bug $bug Bug
     *
     * @return Comment[]
     */
    public function findByBug(Bug $bug): array;

    /**
     * Finds comment by id.
     *
     * @param int $id Id
     *
     * @return Comment|null Comment
     */
    public function findOneById(int $id): ?Comment;
}
