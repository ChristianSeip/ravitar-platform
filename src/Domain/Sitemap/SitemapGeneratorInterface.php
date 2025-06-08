<?php

namespace App\Domain\Sitemap;

interface SitemapGeneratorInterface
{
	/**
	 * @return SitemapEntry[]
	 */
	public function generateEntries(): array;
}