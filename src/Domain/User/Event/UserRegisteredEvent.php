<?php

namespace App\Domain\User\Event;

use App\Domain\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
	public function __construct(public User $user) {}
}