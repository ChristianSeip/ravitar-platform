<?php
namespace App\Tests\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Event\UserRegisteredEvent;
use App\Domain\User\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegistrationServiceTest extends TestCase
{
	private EntityManagerInterface|MockObject $em;
	private UserPasswordHasherInterface|MockObject $hasher;
	private RequestStack|MockObject $requestStack;
	private EventDispatcherInterface|MockObject $dispatcher;
	private RegistrationService $service;

	protected function setUp(): void
	{
		$this->em         = $this->createMock(EntityManagerInterface::class);
		$this->hasher     = $this->createMock(UserPasswordHasherInterface::class);
		$this->dispatcher = $this->createMock(EventDispatcherInterface::class);

		$request = new Request();
		$request->server->set('REMOTE_ADDR', '1.2.3.4');

		$this->requestStack = $this->createMock(RequestStack::class);
		$this->requestStack
			->method('getCurrentRequest')
			->willReturn($request);

		$this->service = new RegistrationService(
			$this->em,
			$this->hasher,
			$this->requestStack,
			$this->dispatcher
		);
	}

	public function testRegisterPersistsUserAndDispatchesEvent(): void
	{
		$user = new User();

		$this->hasher->expects($this->once())
			->method('hashPassword')
			->with($user, 'plain123')
			->willReturn('HASHED');

		$this->em->expects($this->once())
			->method('persist')
			->with($user);
		$this->em->expects($this->once())
			->method('flush');

		$this->dispatcher->expects($this->once())
			->method('dispatch')
			->with($this->callback(function ($evt) use ($user) {
				return $evt instanceof UserRegisteredEvent
					&& $evt->user === $user;
			}));

		$this->service->register($user, 'plain123');

		$this->assertSame('HASHED', $user->getPassword());
		$this->assertContains('ROLE_USER', $user->getRoles());
		$this->assertFalse($user->isVerified());
		$this->assertSame('1.2.3.4', $user->getRegisteredIp());
	}

	public function testRegisterWithoutRequestSetsDefaultIp(): void
	{
		$this->requestStack = $this->createMock(RequestStack::class);
		$this->requestStack
			->method('getCurrentRequest')
			->willReturn(null);

		$this->service = new RegistrationService(
			$this->em,
			$this->hasher,
			$this->requestStack,
			$this->dispatcher
		);

		$user = new User();

		$this->hasher->method('hashPassword')->willReturn('HASHED2');
		$this->em->method('persist');
		$this->em->method('flush');
		$this->dispatcher->method('dispatch');

		$this->service->register($user, 'any');

		$this->assertSame('0.0.0.0', $user->getRegisteredIp());
	}
}
