<?php

namespace App\Domain\Blog\Controller;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Form\PostFormType;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\Repository\TagRepository;
use App\Domain\Blog\Service\TagService;
use App\Domain\Common\Pagination\PaginationService;
use App\Domain\Common\Service\ImageStorageService;
use App\Domain\Common\Service\SlugService;
use App\Domain\User\Service\UserContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostController extends AbstractController
{
	public function __construct(private readonly EntityManagerInterface $em,
															private readonly TagService $tagService,
															private readonly SlugService $slugService,
															private readonly UserContextService $userContext,
															private readonly ImageStorageService $imageStorage,
															private readonly TranslatorInterface $translator)
	{
	}

	/**
	 * Displays a paginated list of all published posts.
	 *
	 * @param Request $request
	 * @param PostRepository $postRepo
	 * @param PaginationService $paginator
	 *
	 * @return Response
	 */
	#[Route('/blog/posts', name: 'blog_post_list')]
	public function list(Request $request, PostRepository $postRepo, PaginationService $paginator): Response
	{
		$total = $postRepo->countAll();
		$pagination = $paginator->paginate($request, $total);
		$posts = $postRepo->findAllPaginated($pagination->limit, $pagination->offset);

		return $this->render('blog/list.html.twig', [
			'title'       => $this->translator->trans('blog.post.list.title', [], 'messages'),
			'posts'       => $posts,
			'pagination'  => $pagination,
			'emptyText' 	=> $this->translator->trans('blog.search.not_found', [], 'messages'),
			'canonical'   => $this->generateUrl('blog_post_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
			'routeParams' => []
		]);
	}

	/**
	 * Creates or edits a blog post.
	 *
	 * @param Request $request
	 * @param PostRepository $postRepo
	 * @param string|null $slug
	 *
	 * @return Response
	 */
	#[Route('/blog/posts/new', name: 'blog_post_create')]
	#[Route('/blog/posts/{slug}/edit', name: 'blog_post_edit')]
	#[IsGranted('ROLE_ADMIN')]
	public function editor(Request $request, PostRepository $postRepo, ?string $slug = null): Response
	{
		$post = $slug ? $postRepo->findOneBy(['slug' => $slug]) : new Post();

		if (!$post) {
			throw $this->createNotFoundException($this->translator->trans('blog.tag.not_found', [], 'messages'));
		}

		$isEdit = $post->getId() !== null;
		$tagsAsString = $isEdit
			? implode(', ', array_map(fn ($tag) => $tag->getName(), $post->getTags()->toArray()))
			: '';

		$form = $this->createForm(PostFormType::class, $post);
		$form->get('tags')->setData($tagsAsString);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			try {
				if (!$post->getSlug()) {
					$post->setSlug($this->slugService->generateSlug($post->getTitle()));
				}

				if (!$post->getAuthor()) {
					$post->setAuthor($this->userContext->getUser());
				}

				$rawTags = $form->get('tags')->getData() ?? '';
				$post->addTags($this->tagService->processTagInput($rawTags));

				$image = $form->get('featuredImage')->getData();
				if ($image) {
					$filename = $this->imageStorage->store($image, $this->getParameter('blog_post_upload_dir'));
					$post->setFeaturedImage($filename);
				}

				$this->em->persist($post);
				$this->em->flush();

				$this->addFlash('success', $isEdit ? $this->translator->trans('blog.post.form.updated', [], 'messages') : $this->translator->trans('blog.post.form.created', [], 'messages'));
				return $this->redirectToRoute('blog_post_edit', ['slug' => $post->getSlug()]);
			}
			catch (RuntimeException $e) {
				$this->addFlash('error', $e->getMessage());
			}
		}

		return $this->render('blog/editor.html.twig', [
			'form'   => $form->createView(),
			'post'   => $post,
			'isEdit' => $isEdit,
		]);
	}

	/**
	 * Displays a single blog post.
	 *
	 * Also loads the previous and next post, if available.
	 *
	 * @param string $slug
	 * @param PostRepository $postRepo
	 *
	 * @return Response
	 */
	#[Route('/blog/posts/{slug}', name: 'blog_post_show')]
	public function __invoke(string $slug, PostRepository $postRepo): Response
	{
		$post = $postRepo->findOneBy(['slug' => $slug]);

		if (!$post || $post->isDeleted()) {
			throw $this->createNotFoundException($this->translator->trans('blog.post.not_found', [], 'messages'));
		}

		$neighbors = $postRepo->findPostNeighbors($post);

		return $this->render('blog/show.html.twig', [
			'post' => $post,
			'previousPost' => $neighbors['previous'],
			'nextPost' => $neighbors['next'],
		]);
	}

	/**
	 * Displays posts by tag, paginated.
	 *
	 * @param string $slug
	 * @param Request $request
	 * @param PostRepository $postRepo
	 * @param TagRepository $tagRepo
	 * @param PaginationService $paginator
	 *
	 * @return Response
	 */
	#[Route('/blog/posts/tag/{slug}', name: 'blog_post_by_tag')]
	public function showByTag(string $slug, Request $request, PostRepository $postRepo, TagRepository $tagRepo, PaginationService $paginator): Response {
		$tag = $tagRepo->findOneBy(['slug' => $slug]);
		if (!$tag) {
			throw $this->createNotFoundException($this->translator->trans('blog.tag.not_found', [], 'messages'));
		}

		$total = $postRepo->countByTagSlug($slug);
		$pagination = $paginator->paginate($request, $total);

		$posts = $postRepo->findByTagSlugPaginated($slug, $pagination->limit, $pagination->offset);

		return $this->render('blog/list.html.twig', [
			'title'     => $this->translator->trans('blog.search.title', [], 'messages') . ' (' . $this->translator->trans('blog.tag.tag', [], 'messages') . ': ' . $tag->getName() . ')',
			'posts'     => $posts,
			'pagination' => $pagination,
			'emptyText' => $this->translator->trans('blog.search.not_found', [], 'messages'),
			'canonical' => $this->generateUrl('blog_post_by_tag', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL),
			'routeParams' => ['slug' => $slug]
		]);
	}
}
