<?php

namespace App\Domain\Blog\Controller;

use App\Domain\Blog\Entity\Comment;
use App\Domain\Blog\Form\CommentFormType;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\User\Service\UserContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{

	public function __construct(private readonly EntityManagerInterface $em, private readonly UserContextService $userContext)
	{
	}

	#[Route('/post/{id}/comment', name: 'comment_add', methods: ['POST'])]
	public function add(int $id, Request $request, PostRepository $postRepository): JsonResponse
	{
		if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
			return new JsonResponse(['success' => false, 'error' => 'Du musst dich anmelden, um Kommentare zu schreiben.'], Response::HTTP_UNAUTHORIZED);
		}

		$post = $postRepository->find($id);
		if (!$post) {
			return new JsonResponse(['success' => false, 'error' => 'Beitrag nicht gefunden'], Response::HTTP_NOT_FOUND);
		}

		$comment = new Comment();

		$parentId = $request->request->get('parent_id');
		if ($parentId) {
			$parent = $this->em->getRepository(Comment::class)->find($parentId);
			if ($parent) {
				$comment->setParent($parent);
			}
		}

		$form = $this->createForm(CommentFormType::class, $comment);
		$form->handleRequest($request);

		if (!$form->isValid()) {
			return new JsonResponse([
				'success' => false,
				'errors' => $this->extractFormErrors($form),
			], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$comment
			->setPost($post)
			->setAuthor($this->userContext->getUser())
			->setCreatedAt(new \DateTimeImmutable())
			->setIp($request->getClientIp());

		$this->em->persist($comment);
		$this->em->flush();

		$html = $this->renderView('blog/_comment.html.twig', ['comment' => $comment]);

		return new JsonResponse(['success' => true, 'html' => $html]);
	}

	private function extractFormErrors(FormInterface $form): array
	{
		$errors = [];
		foreach ($form->getErrors(true) as $error) {
			$origin = $error->getOrigin();
			$fieldName = $origin instanceof FormInterface ? $origin->getName() : 'form';
			$errors[$fieldName][] = $error->getMessage();
		}
		return $errors;
	}

}
