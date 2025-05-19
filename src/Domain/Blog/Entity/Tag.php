<?php
namespace App\Domain\Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Blog\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'blog_tag')]
class Tag
{
	#[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 100, unique: true)]
	private string $name;

	#[ORM\Column(type: 'string', length: 100, unique: true)]
	private string $slug;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getSlug(): string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;
		return $this;
	}
}