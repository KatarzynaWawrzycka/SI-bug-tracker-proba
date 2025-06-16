<?php

namespace App\Repository;

use App\Entity\Bug;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByBug(Bug $bug): array
    {
        return $this->createQueryBuilder('comment')
            ->select(
                'partial comment.{id, content, createdAt, updatedAt}',
                'partial author.{id, email}'
            )
            ->join('comment.author', 'author')
            ->andWhere('comment.bug = :bug')
            ->setParameter('bug', $bug)
            ->orderBy('comment.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
