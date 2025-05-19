<?php

namespace App\Domain\Common\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugService implements SlugServiceInterface
{
	public function __construct(private readonly SluggerInterface $slugger, private readonly ManagerRegistry  $doctrine)
	{
	}

	public function generateSlug(string $text): string
	{
		return mb_strtolower((string)$this->slugger->slug($text));
	}

	public function generateUniqueSlug(string $text, string $entityClass, string $field = 'slug'): string
	{
		$baseSlug = $this->generateSlug($text);
		$slug = $baseSlug;
		$i = 1;

		$repo = $this->doctrine->getRepository($entityClass);
		while ($repo->findOneBy([$field => $slug]) !== null) {
			$slug = $baseSlug . '-' . $i++;
		}

		return $slug;
	}

	public function resolve(string $entityClass, string $slug, string $field = 'slug'): ?object
	{
		return $this->doctrine->getRepository($entityClass)->findOneBy([$field => $slug]);
	}
}