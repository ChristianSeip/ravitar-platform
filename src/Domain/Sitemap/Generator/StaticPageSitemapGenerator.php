<?php

namespace App\Domain\Sitemap\Generator;

use App\Domain\Sitemap\SitemapEntry;
use App\Domain\Sitemap\SitemapGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StaticPageSitemapGenerator implements SitemapGeneratorInterface
{
	public function __construct(
		private readonly UrlGeneratorInterface $urlGenerator
	) {}

	public function generateEntries(): array
	{
		$paths = [
			['route' => 'landing',      					'priority' => 1.0],
			['route' => 'nutzungsbedingungen',    'priority' => 0.3],
			['route' => 'impressum',  						'priority' => 0.3],
			['route' => 'datenschutz',						'priority' => 0.3],
		];

		$entries = [];

		foreach ($paths as $item) {
			$entries[] = new SitemapEntry(
				loc: $this->urlGenerator->generate($item['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
				lastmod: null,
				changefreq: 'yearly',
				priority: $item['priority']
			);
		}

		return $entries;
	}
}
