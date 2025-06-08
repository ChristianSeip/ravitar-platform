<?php

namespace App\Domain\Sitemap;

class SitemapEntry
{
	public function __construct(
		public readonly string $loc,
		public readonly ?\DateTimeInterface $lastmod = null,
		public readonly ?string $changefreq = null,
		public readonly ?float $priority = null
	)
	{}
}