<?php
/**
 *Bug repository.
 */

namespace App\Repository;

use App\Entity\Bug;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
class BugRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

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
     * @return \Doctrine\ORM\QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial bug.{id, title, description, createdAt, updatedAt}',
                'partial category.{id, title}'
            )
            ->join('bug.category', 'category')
            ->orderBy('bug.updatedAt', 'DESC');
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('bug');
    }

    /**
     * Save entity.
     *
     * @param Bug $bug Bug entity
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
        //assert($this->_em instanceof EntityManager);
        $this->_em->remove($bug);
        $this->_em->flush();
    }
}