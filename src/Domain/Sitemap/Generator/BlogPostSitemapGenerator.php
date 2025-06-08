<?php

namespace App\Domain\Sitemap\Generator;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Sitemap\SitemapEntry;
use App\Domain\Sitemap\SitemapGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogPostSitemapGenerator implements SitemapGeneratorInterface
{
	public function __construct(
		private readonly PostRepository $postRepository,
		private readonly UrlGeneratorInterface $urlGenerator
	) {}

	public function generateEntries(): array
	{
		$entries = [];

		foreach ($this->postRepository->findAllPublished() as $post) {
			$entries[] = new SitemapEntry(
				loc: $this->urlGenerator->generate('blog_post_show', [
					'slug' => $post->getSlug(),
				], UrlGeneratorInterface::ABSOLUTE_URL),
				lastmod: $post->getUpdatedAt(),
				changefreq: 'monthly',
				priority: 0.7
			);
		}

		return $entries;
	}
}
