<?php

namespace App\Domain\Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\User\Entity\User;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'blog_post')]
class Post
{
	#[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $author = null;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $createdAt;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $updatedAt = null;

	#[ORM\Column(type: 'string', length: 255)]
	private string $title;

	#[ORM\Column(type: 'string', length: 255, unique: true)]
	private ?string $slug = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $excerpt = null;

	#[ORM\Column(type: 'text')]
	private string $content;

	#[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
	#[ORM\JoinTable(name: 'blog_post_tag')]
	private Collection $tags;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $featuredImage = null;

	#[ORM\Column(type: 'boolean')]
	private bool $isDeleted = false;

	#[ORM\Column(type: 'smallint')]
	private int $gridRows = 1;

	#[ORM\Column(type: 'smallint')]
	private int $gridCols = 1;

	#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
	private Collection $comments;

	public function __construct()
	{
		$this->createdAt = new \DateTimeImmutable();
		$this->tags = new ArrayCollection();
		$this->comments = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getCreatedAt(): \DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function setCreatedAt(\DateTimeImmutable $dt): self
	{
		$this->updatedAt = $dt;
		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(\DateTimeImmutable $dt): self
	{
		$this->updatedAt = $dt;
		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;
		return $this;
	}

	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;
		return $this;
	}

	public function getExcerpt(): ?string
	{
		return $this->excerpt;
	}

	public function setExcerpt(?string $excerpt): self
	{
		$this->excerpt = $excerpt;
		return $this;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): self
	{
		$this->content = $content;
		return $this;
	}

	public function getTags(): Collection
	{
		return $this->tags;
	}

	public function addTags(array $tags): self
	{
		foreach ($tags as $tag) {
			if (!$this->tags->contains($tag)) {
				$this->tags->add($tag);
			}
		}
		return $this;
	}

	public function removeTag(Tag $tag): self
	{
		$this->tags->removeElement($tag);
		return $this;
	}

	public function getFeaturedImage(): ?string
	{
		return $this->featuredImage;
	}

	public function setFeaturedImage(?string $path): self
	{
		$this->featuredImage = $path;
		return $this;
	}

	public function isDeleted(): bool
	{
		return $this->isDeleted;
	}

	public function setIsDeleted(bool $deleted): self
	{
		$this->isDeleted = $deleted;
		return $this;
	}

	public function getGridRows(): int
	{
		return $this->gridRows;
	}

	public function setGridRows(int $rows): self
	{
		$this->gridRows = $rows;
		return $this;
	}

	public function getGridCols(): int
	{
		return $this->gridCols;
	}

	public function setGridCols(int $cols): self
	{
		$this->gridCols = $cols;
		return $this;
	}

	public function getComments(): Collection
	{
		return $this->comments;
	}
}
