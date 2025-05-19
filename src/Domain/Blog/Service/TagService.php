<?php

namespace App\Domain\Blog\Service;

use App\Domain\Blog\Entity\Tag;
use App\Domain\Blog\Repository\TagRepository;
use App\Domain\Common\Service\SlugService;
use Doctrine\ORM\EntityManagerInterface;

class TagService {
	public function __construct(private TagRepository $tagRepo, private EntityManagerInterface $em, private SlugService $slugService)
	{
	}

	/**
	 * Processes raw tag input (e.g., from a form), returns an array of Tag entities.
	 * New tags are created and persisted if they do not yet exist.
	 *
	 * @param string $rawInput
	 *
	 * @return Tag[]
	 */
	public function processTagInput(string $rawInput): array
	{
		$tagNames = $this->parseTagInput($rawInput);
		$slugs = array_map([$this->slugService, 'generateSlug'], $tagNames);
		$existingTags = $this->getExistingTagsBySlugs($slugs);

		return $this->buildTagList($tagNames, $existingTags);
	}

	/**
	 * Parses a raw comma-separated string into a clean, unique array of tag names.
	 *
	 * @param string $input
	 *
	 * @return string[]
	 */
	private function parseTagInput(string $input): array
	{
		$tags = explode(',', $input);
		$tags = array_map('trim', $tags);
		$tags = array_filter($tags);

		$unique = [];
		foreach ($tags as $tag) {
			$lower = mb_strtolower($tag);
			if (!in_array($lower, array_map('mb_strtolower', $unique))) {
				$unique[] = $tag;
			}
		}

		return $unique;
	}

	/**
	 * Retrieves existing tags from the repository based on their slugs.
	 * Returns an associative array for fast lookup.
	 *
	 * @param string[] $slugs
	 *
	 * @return array<string, Tag>
	 */
	private function getExistingTagsBySlugs(array $slugs): array
	{
		$tags = $this->tagRepo->findBy(['slug' => $slugs]);
		$map = [];
		foreach ($tags as $tag) {
			$map[$tag->getSlug()] = $tag;
		}
		return $map;
	}

	/**
	 * Creates new Tag entities for names that do not yet exist and merges with existing tags.
	 * New tags are persisted but not yet flushed.
	 *
	 * @param string[] 						$names
	 * @param array<string, Tag> 	$existing
	 *
	 * @return Tag[]
	 */
	private function buildTagList(array $names, array $existing): array
	{
		$result = [];

		foreach ($names as $name) {
			$slug = $this->slugService->generateSlug($name);
			if (isset($existing[$slug])) {
				$result[] = $existing[$slug];
			}
			else {
				$tag = new Tag();
				$tag->setName($name);
				$tag->setSlug($slug);
				$this->em->persist($tag);
				$result[] = $tag;
			}
		}

		return $result;
	}
}