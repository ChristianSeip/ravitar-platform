<?php

namespace App\Domain\Page\Controller;

use App\Domain\Blog\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController {

	#[Route(['/', '/landing'], name: 'landing')]
	public function index(PostRepository $postRepo): Response
	{
		$latestBlogPosts = $postRepo->findLatest(6);
		return $this->render('page/landing.html.twig', [
			'latestBlogPosts' => $latestBlogPosts,
		]);

	}

}