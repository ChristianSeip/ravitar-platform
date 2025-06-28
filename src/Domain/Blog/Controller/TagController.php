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
use Symfony\Contracts\Translation\TranslatorInterface;

class TagController extends AbstractController
{
	public function __construct(private readonly TranslatorInterface $translator)
	{
	}

	#[Route('/blog/tag/{slug}', name: 'blog_tag_overview')]
	public function showByTag(string $slug, Request $request, PostRepository $postRepo, TagRepository $tagRepo, PaginationService $paginator): Response {
		$tag = $tagRepo->findOneBy(['slug' => $slug]);

		$posts = [];
		$pagination = null;
		$total = 0;

		if ($tag) {
			$total = $postRepo->countByTagSlug($slug);
			$pagination = $paginator->paginate($request, $total);
			$posts = $postRepo->findByTagSlugPaginated($slug, $pagination->limit, $pagination->offset);
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
