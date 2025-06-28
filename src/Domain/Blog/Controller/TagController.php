<?php

namespace App\Domain\Blog\Controller;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\Repository\TagRepository;
use App\Domain\Common\Pagination\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagController extends AbstractController
{
	public function __construct(private readonly TranslatorInterface $translator, private readonly CacheInterface $cache)
	{
	}

	#[Route('/blog/tag/{slug}', name: 'blog_tag_overview')]
	public function showByTag(string $slug, Request $request, PostRepository $postRepo, TagRepository $tagRepo, PaginationService $paginator): Response {
		$tag = $this->cache->get("tag_by_slug_$slug", function (ItemInterface $item) use ($tagRepo, $slug) {
			$item->expiresAfter(3600);
			return $tagRepo->findOneBy(['slug' => $slug]);
		});

		$posts = [];
		$pagination = null;

		if ($tag) {
			$page = max(1, (int) $request->query->get('page', 1));

			$cacheKeyPosts = "posts_by_tag_{$slug}_page_{$page}";
			$posts = $this->cache->get($cacheKeyPosts, function (ItemInterface $item) use ($postRepo, $slug, $paginator, $request) {
				$item->expiresAfter(300);
				$total = $postRepo->countByTagSlug($slug);
				$pagination = $paginator->paginate($request, $total);
				return $postRepo->findByTagSlugPaginated($slug, $pagination->limit, $pagination->offset);
			});

			$total = $postRepo->countByTagSlug($slug);
			$pagination = $paginator->paginate($request, $total);
		}

		return $this->render('blog/tag_view.html.twig', [
			'tag'        => $tag,
			'posts'      => $posts,
			'pagination' => $pagination,
			'emptyText'  => $this->translator->trans('blog.tag.not_found', [], 'messages'),
			'canonical'  => $this->generateUrl('blog_tag_overview', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL),
		]);
	}


}
