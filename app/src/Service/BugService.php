<?php
/**
 * Bug service.
 */

namespace App\Service;

use App\Dto\BugListFiltersDto;
use App\Dto\BugListInputFiltersDto;
use App\Entity\Bug;
use App\Entity\User;
use App\Repository\BugRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class BugService.
 */
class BugService implements BugServiceInterface
{
    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param BugRepository            $bugRepository   Bug repository
     * @param PaginatorInterface       $paginator       Paginator
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly BugRepository $bugRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                    $page    Page number
     * @param User|null              $user    Bug author
     * @param BugListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, ?User $user, BugListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if (null !== $user) {
            $query = $this->bugRepository->queryByAuthor($user, $filters);
        } else {
            $query = $this->bugRepository->queryPublicBugs($filters);
        }

        return $this->paginator->paginate(
            $query,
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['bug.id', 'bug.createdAt', 'bug.updatedAt', 'bug.title', 'bug.description', 'category.title'],
                'defaultSortFieldName' => 'bug.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
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
        $bug->setCreatedAt(new \DateTimeImmutable());
        if (null !== $bug->getId()) {
            $bug->setUpdatedAt(new \DateTimeImmutable());
        }
        $this->bugRepository->save($bug);
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
        $this->bugRepository->delete($bug);
    }

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Prepare filters for the bug list.
     *
     * @param BugListInputFiltersDto $filters Raw filters from request
     *
     * @return BugListFiltersDto Result filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(BugListInputFiltersDto $filters): BugListFiltersDto
    {
        return new BugListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
        );
    }
}
