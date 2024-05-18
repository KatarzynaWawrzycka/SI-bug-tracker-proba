<?php
/**
 * Bug service.
 */

namespace App\Service;

use App\Entity\Bug;
use App\Repository\BugRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class BugService.
 */
class BugService implements BugServiceInterface
{
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
     * Constructor.
     *
     * @param BugRepository     $bugRepository Bug repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(private readonly BugRepository $bugRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->bugRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Bug $bug Bug entity
     */
    public function save(Bug $bug): void
    {
        if (null === $bug->getId()) {
            $bug->setCreatedAt(new \DateTimeImmutable());
        }
        $bug->setUpdatedAt(new \DateTimeImmutable());

        $this->bugRepository->save($bug);
    }

    public function delete(Bug $bug): void
    {
        //assert($this->_em instanceof EntityManager);
        $this->bugRepository->delete($bug);
    }
}