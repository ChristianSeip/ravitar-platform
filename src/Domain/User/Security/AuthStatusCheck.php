<?php

namespace App\Domain\User\Security;

use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class AuthStatusCheck implements UserCheckerInterface
{

	private TranslatorInterface $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	public function checkPreAuth(UserInterface $user): void
	{
		if (!$user instanceof User) {
			return;
		}

		if ($user->isLocked()) {
			throw new CustomUserMessageAccountStatusException($this->translator->trans('user.login.error.locked'));
		}
	}

	public function checkPostAuth(UserInterface $user): void
	{
		if (!$user instanceof User) {
			return;
		}

		if (!$user->isVerified()) {
			throw new CustomUserMessageAccountStatusException($this->translator->trans('user.login.error.not_verified'));
		}
	}
}