<?php

namespace App\Domain\Blog\Entity;

use App\Domain\User\Entity\User;
use App\Domain\Blog\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comment')]
class Comment
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?Post $post = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
	private ?User $author = null;

	#[ORM\Column(type: 'text')]
	#[Assert\NotBlank]
	private string $message;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $createdAt;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $updatedAt = null;

	#[ORM\Column(type: 'string', length: 45)]
	private string $ip;

	#[ORM\Column(type: 'boolean')]
	private bool $isDeleted = false;

	#[ORM\ManyToOne(targetEntity: self::class)]
	#[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
	private ?self $parent = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getPost(): ?Post
	{
		return $this->post;
	}

	public function setPost(Post $post): self
	{
		$this->post = $post;
		return $this;
	}

	public function getAuthor(): ?User
	{
		return $this->author;
	}

	public function setAuthor(User $author): self
	{
		$this->author = $author;
		return $this;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function setMessage(string $message): self
	{
		$this->message = $message;
		return $this;
	}

	public function getCreatedAt(): \DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function setCreatedAt(\DateTimeImmutable $createdAt): self
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
	{
		$this->updatedAt = $updatedAt;
		return $this;
	}

	public function getIp(): string
	{
		return $this->ip;
	}

	public function setIp(string $ip): self
	{
		$this->ip = $ip;
		return $this;
	}

	public function isDeleted(): bool
	{
		return $this->isDeleted;
	}

	public function delete(): self
	{
		$this->isDeleted = true;
		return $this;
	}

	public function restore(): self
	{
		$this->isDeleted = false;
		return $this;
	}

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function setParent(?self $parent): self
	{
		$this->parent = $parent;
		return $this;
	}

}