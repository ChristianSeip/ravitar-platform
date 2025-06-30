<?php


namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserContextService
{
	public function __construct(private Security $security)
	{
	}

	/**
	 * Returns the current logged-in user as App\Domain\User\Entity\User.
	 *
	 * @throws AccessDeniedException
	 */
	public function getUser(): User
	{
		$user = $this->security->getUser();

		if (!$user instanceof User) {
			throw new AccessDeniedException('No authenticated user or invalid user type.');
		}

		return $user;
	}

	/**
	 * Returns the user if available and of correct type, or null.
	 */
	public function getUserOrNull(): ?User
	{
		$user = $this->security->getUser();
		return $user instanceof User ? $user : null;
	}

	public function isAdmin(): bool
	{
		$user = $this->getUserOrNull();
		return $user instanceof User && $user->isAdmin();
	}

	public function isUser(): bool
	{
		$user = $this->getUserOrNull();
		return $user instanceof User && $user->isUser();
	}

	public function isGuest(): bool
	{
		return $this->getUserOrNull() === null;
	}
}