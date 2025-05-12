<?php

/**
 * Bug repository.
 */

namespace App\Repository;

use App\Dto\BugListFiltersDto;
use App\Entity\Bug;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class BugRepository.
 *
 * @method Bug|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bug|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bug[]    findAll()
 * @method Bug[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Bug>
 */
class BugRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bug::class);
    }

    /**
     * Query all records.
     *
     * @param User                   $author  User entity
     * @param BugListFiltersDto|null $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $author, ?BugListFiltersDto $filters = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('bug')
            ->select(
                'partial bug.{id, createdAt, updatedAt, title, description}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('bug.category', 'category')
            ->leftJoin('bug.tags', 'tags');

        if (!in_array('ROLE_ADMIN', $author->getRoles(), true)) {
            $qb->andWhere('bug.author = :author')
                ->setParameter('author', $author);
        }

        if ($filters instanceof BugListFiltersDto) {
            $qb = $this->applyFiltersToList($qb, $filters);
        }

        return $qb;
    }

    /**
     * Query public bugs (for unauthenticated users).
     *
     * @param BugListFiltersDto|null $filters Filters
     */
    public function queryPublicBugs(?BugListFiltersDto $filters = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('bug')
            ->select(
                'partial bug.{id, createdAt, updatedAt, title, description}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('bug.category', 'category')
            ->leftJoin('bug.tags', 'tags')
            ->orderBy('bug.updatedAt', 'DESC');

        if ($filters instanceof BugListFiltersDto) {
            $qb = $this->applyFiltersToList($qb, $filters);
        }

        return $qb;
    }

    /**
     * Count bugs by category.
     *
     * @param Category $category Category
     *
     * @return int Number of bugs in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('bug.id'))
            ->where('bug.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Bug $bug Bug entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Bug $bug): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->persist($bug);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Bug $bug Bug entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Bug $bug): void
    {
        assert($this->_em instanceof EntityManager);
        $this->_em->remove($bug);
        $this->_em->flush();
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder      $queryBuilder Query builder
     * @param BugListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, BugListFiltersDto $filters): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters->category);
        }

        return $queryBuilder;
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('bug');
    }
}
