<?php

namespace App\Domain\User\Entity;

use App\Domain\User\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 100, unique: true)]
    private string $username;

		#[ORM\Column(length: 100, unique: true)]
		private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private \DateTimeImmutable $registeredAt;

    #[ORM\Column(length: 40)]
    private string $registeredIp;

    #[ORM\Column]
    private bool $isLocked = false;

    #[ORM\Column]
    private bool $isVerified = false;

		#[ORM\Column(type: 'json')]
		private array $roles = [];

		#[ORM\OneToOne(targetEntity: UserProfile::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
		private UserProfile $profile;

		public function __construct()
		{
			$this->registeredAt = new \DateTimeImmutable();
			$this->profile = new UserProfile();
			$this->profile->setUser($this);
		}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

		public function getEmail(): ?string
		{
			return $this->email;
		}

		public function setEmail(string $email): static
		{
			$this->email = $email;

			return $this;
		}

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getRegisteredIp(): ?string
    {
        return $this->registeredIp;
    }

    public function setRegisteredIp(string $registeredIp): static
    {
        $this->registeredIp = $registeredIp;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

		public function getRoles(): array
		{
			return array_unique($this->roles);
		}

		public function setRoles(array $roles): self
		{
			$this->roles = $roles;

			return $this;
		}

		public function addRoles(array $roles): self
		{
			foreach ($roles as $role) {
				if (!in_array($role, $this->roles, true)) {
					$this->roles[] = $role;
				}
			}

			return $this;
		}

		public function removeRoles(array $roles): self
		{
			foreach ($roles as $role) {
				$key = array_search($role, $this->roles, true);
				if (false !== $key) {
					unset($this->roles[$key]);
				}
			}
			$this->roles = array_values($this->roles);

			return $this;
		}

		public function isAdmin(): bool
		{
			return in_array('ROLE_ADMIN', $this->getRoles(), true);
		}

		public function isUser(): bool
		{
			return in_array('ROLE_USER', $this->getRoles(), true);
		}

		public function getUserIdentifier(): string
		{
			return $this->email;
		}

		public function eraseCredentials(): void
		{
			// If you store any temporary, sensitive data on the user, clear it here
			// $this->plainPassword = null;
		}

		public function getProfile(): UserProfile
		{
			return $this->profile;
		}

		public function setProfile(UserProfile $profile): static
		{
			$this->profile = $profile;
			return $this;
		}
}
