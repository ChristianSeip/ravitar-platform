<?php
namespace App\Domain\Common\Service;

interface SlugServiceInterface
{
	/**
	 * Creates a “clean” slug from any text.
	 */
	public function generateSlug(string $text): string;

	/**
	 * Creates a unique slug for an entity by appending a number (–1, –2, …) in case of conflict.
	 *
	 * @param string       $text
	 * @param class-string $entityClass
	 * @param string       $field
	 *
	 * @return string
	 */
	public function generateUniqueSlug(string $text, string $entityClass, string $field = 'slug'): string;

	/**
	 * Loads an entity based on its slug.
	 *
	 * @param class-string $entityClass
	 * @param string       $slug
	 * @param string       $field
	 *
	 * @return object|null
	 */
	public function resolve(string $entityClass, string $slug, string $field = 'slug'): ?object;
}