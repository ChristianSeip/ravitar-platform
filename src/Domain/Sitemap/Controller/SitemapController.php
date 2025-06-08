<?php

namespace App\Domain\Sitemap\Controller;

use App\Domain\Sitemap\Builder\SitemapBuilderService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class SitemapController
{
	#[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
	public function __invoke(SitemapBuilderService $builder, CacheInterface $cache): Response
	{
		$xml = $cache->get('sitemap.xml', function (ItemInterface $item) use ($builder) {
			$item->expiresAfter(86400);
			return $builder->build();
		});

		return new Response(
			$xml,
			Response::HTTP_OK,
			[
				'Content-Type' => 'application/xml',
				'Cache-Control' => 'public, max-age=86400'
			]
		);
	}
}
