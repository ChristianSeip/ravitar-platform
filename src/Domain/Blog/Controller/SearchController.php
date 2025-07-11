<?php

namespace App\Domain\Blog\Controller;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\Service\SearchParserService;
use App\Domain\Common\Pagination\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController extends AbstractController {
	public function __construct(private readonly PostRepository $postRepo, private readonly SearchParserService $parser, private readonly TranslatorInterface $translator)
	{
	}

	/**
	 * Performs a full-text search on blog posts using PostgreSQL tsquery.
	 *
	 * Renders a paginated list of results or an empty state if no query or no matches are found.
	 *
	 * @param Request $request
	 * @param PaginationService $paginator
	 *
	 * @return Response
	 */
	#[Route('/blog/search', name: 'blog_post_search')]
	public function __invoke(Request $request, PaginationService $paginator): Response
	{
		$rawQuery = trim((string) $request->query->get('q', ''));
		$page = max(1, (int) $request->query->get('page', 1));

		if ($rawQuery === '') {
			return $this->render('blog/list.html.twig', [
				'posts'     => [],
				'title'     => $this->translator->trans('blog.search.title', [], 'messages'),
				'canonical' => $request->getUri(),
				'pagination' => null,
				'emptyText' => $this->translator->trans('blog.search.no_keyword', [], 'messages'),
			]);
		}

		$tsQuery = $this->parser->parse($rawQuery);
		$total   = $this->postRepo->countByTsQuery($tsQuery);
		$pagination = $paginator->paginate($request, $total);

		$posts = $this->postRepo->findByTsQuery(
			$tsQuery,
			$pagination->limit,
			$pagination->offset
		);

		return $this->render('blog/list.html.twig', [
			'posts'      => $posts,
			'pagination' => $pagination,
			'title'      => $this->translator->trans('blog.search.title', [], 'messages') . ' (' . $rawQuery . ')',
			'canonical'  => $request->getUri(),
			'emptyText'  => $this->translator->trans('blog.search.no_result', [], 'messages'),
			'routeParams' => ['q' => $rawQuery],
		]);
	}
}