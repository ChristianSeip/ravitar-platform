<?php

namespace App\Domain\User\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\Form\RegistrationFormType;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Security\EmailVerifier;
use App\Domain\User\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
	public function __construct(private EmailVerifier $emailVerifier, private RegistrationService $registrationService)
	{
	}

	#[Route('/register', name: 'app_register')]
	public function register(Request $request, TranslatorInterface $translator): Response
	{
		if ($this->getUser()) {
			return $this->redirectToRoute('landing');
		}
		$user = new User();
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$plain = $form->get('plainPassword')->getData();
			$this->registrationService->register($user, $plain);

			$this->addFlash('success', $translator->trans('user.registration.flash.check_email', [], 'messages'));
			return $this->redirectToRoute('landing');
		}

		if ($form->isSubmitted()) {
			return new Response(
				$this->renderView('user/register.html.twig', [
					'registrationForm' => $form->createView(),
				]),
				Response::HTTP_UNPROCESSABLE_ENTITY
			);
		}

		return $this->render('user/register.html.twig', [
			'registrationForm' => $form->createView(),
		]);
	}

	#[Route('/verify/email', name: 'app_verify_email')]
	public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response {
		$id = $request->query->getInt('id');
		if (0 === $id || null === $user = $userRepository->find($id)) {
			return $this->redirectToRoute('app_register');
		}

		try {
			$this->emailVerifier->handleEmailConfirmation($request, $user);
		}
		catch (VerifyEmailExceptionInterface $e) {
			$this->addFlash('error', $translator->trans($e->getReason(), [], 'VerifyEmailBundle'));
			return $this->redirectToRoute('app_register');
		}

		$this->addFlash('success', $translator->trans('user.registration.flash.verified', [], 'messages'));
		return $this->redirectToRoute('app_login');
	}
}
