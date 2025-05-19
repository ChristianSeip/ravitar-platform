<?php

namespace App\Domain\Common\Pagination;

use Symfony\Component\HttpFoundation\Request;

class PaginationService {
	private int $defaultLimit;

	public function __construct(int $defaultLimit = 10)
	{
		$this->defaultLimit = $defaultLimit;
	}

	public function paginate(Request $request, int $totalItems, ?int $limit = null): PaginationResult
	{
		$limit = $limit ?? $this->defaultLimit;
		$page = max(1, (int)$request->query->get('page', 1));
		$totalPages = (int)ceil($totalItems / $limit);

		$page = min($page, max(1, $totalPages));

		return new PaginationResult(
			page: $page,
			limit: $limit,
			offset: ($page - 1) * $limit,
			totalItems: $totalItems,
			totalPages: $totalPages
		);
	}
}
