<?php

/**
 * Category repository.
 */

namespace App\Repository;

use App\Entity\Bug;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CommentRepository.
 *
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Find comment assigned to bug.
     *
     * @param Bug $bug Bug
     *
     * @return array of comments
     */
    public function findByBug(Bug $bug): array
    {
        return $this->createQueryBuilder('comment')
            ->join('comment.author', 'author')
            ->andWhere('comment.bug = :bug')
            ->setParameter('bug', $bug)
            ->orderBy('comment.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
