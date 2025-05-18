<?php

namespace App\Domain\User\EventListener;

use App\Domain\User\Event\UserRegisteredEvent;
use App\Domain\User\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendConfirmationEmailListener
{
	public function __construct(private EmailVerifier $emailVerifier, private TranslatorInterface $translator, private string $fromAddress, private string $fromName,)
	{
	}

	public function onUserRegistered(UserRegisteredEvent $event): void
	{
		$user = $event->user;
		$email = (new TemplatedEmail())
			->from(new Address($this->fromAddress, $this->fromName))
			->to($user->getEmail())
			->subject($this->translator->trans('user.registration.email.subject', [], 'messages'))
			->htmlTemplate('user/confirmation_email.html.twig')
			->context(['user' => $user]);
		$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
	}
}
