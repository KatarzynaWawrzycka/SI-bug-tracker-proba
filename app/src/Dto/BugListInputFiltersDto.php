<?php
/**
 * Bug list input filters DTO.
 */

namespace App\Dto;

/**
 * Class BugListInputFiltersDto.
 */
class BugListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $categoryId Category identifier
     */
    public function __construct(public readonly ?int $categoryId = null)
    {
    }
}