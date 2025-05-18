<?php
namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Domain\User\Event\UserRegisteredEvent;

class RegistrationService
{
	public function __construct(
		private EntityManagerInterface $em,
		private UserPasswordHasherInterface $hasher,
		private RequestStack $requestStack,
		private EventDispatcherInterface $dispatcher,
	) {}

	public function register(User $user, string $plainPassword): void
	{
		$user->setPassword($this->hasher->hashPassword($user, $plainPassword));
		$user->setRoles(['ROLE_USER']);
		$user->setIsVerified(false);
		$ip = $this->requestStack->getCurrentRequest()?->getClientIp() ?? '0.0.0.0';
		$user->setRegisteredIp($ip);
		$this->em->persist($user);
		$this->em->flush();
		$this->dispatcher->dispatch(new UserRegisteredEvent($user));
	}
}
