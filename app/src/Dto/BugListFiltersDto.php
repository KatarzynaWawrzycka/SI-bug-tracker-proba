<?php

/**
 * Bug list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;

/**
 * Class BugListFiltersDto.
 */
class BugListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null $category Category entity
     */
    public function __construct(public readonly ?Category $category)
    {
    }
}
