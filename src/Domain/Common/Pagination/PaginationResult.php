<?php


namespace App\Domain\Common\Pagination;

class PaginationResult {
	public function __construct(public readonly int $page, public readonly int $limit, public readonly int $offset, public readonly int $totalItems, public readonly int $totalPages)
	{
	}

	public function hasPrevious(): bool
	{
		return $this->page > 1;
	}

	public function hasNext(): bool
	{
		return $this->page < $this->totalPages;
	}
}
