<?php

namespace App\Domain\Sitemap\Builder;

use App\Domain\Sitemap\SitemapEntry;
use App\Domain\Sitemap\SitemapGeneratorInterface;

class SitemapBuilderService
{
	/**
	 * @param iterable<SitemapGeneratorInterface> $generators
	 */
	public function __construct(
		private readonly iterable $generators
	) {}

	public function build(): string
	{
		$entries = [];

		/** @var SitemapGeneratorInterface $generator */
		foreach ($this->generators as $generator) {
			$entries = [...$entries, ...$generator->generateEntries()];
		}

		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		/** @var SitemapEntry $entry */
		foreach ($entries as $entry) {
			$url = $xml->addChild('url');
			$url->addChild('loc', htmlspecialchars($entry->loc));

			if ($entry->lastmod) {
				$url->addChild('lastmod', $entry->lastmod->format('Y-m-d'));
			}

			if ($entry->changefreq) {
				$url->addChild('changefreq', $entry->changefreq);
			}

			if ($entry->priority !== null) {
				$url->addChild('priority', number_format($entry->priority, 1));
			}
		}

		return $xml->asXML();
	}
}
