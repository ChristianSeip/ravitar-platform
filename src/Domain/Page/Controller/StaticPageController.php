<?php

namespace App\Domain\Page\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{
	#[Route('/impressum',      				name: 'impressum',  				 defaults: ['slug' => 'impressum'])]
	#[Route('/datenschutz',    				name: 'datenschutz',				 defaults: ['slug' => 'datenschutz'])]
	#[Route('/nutzungsbedingungen', 	name: 'nutzungsbedingungen', defaults: ['slug' => 'nutzungsbedingungen'])]
	public function show(string $slug = 'landing'): Response
	{
		return $this->render("page/{$slug}.html.twig");
	}
}