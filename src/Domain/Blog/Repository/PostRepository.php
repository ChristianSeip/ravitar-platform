<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}

	public function findByTagSlug(string $slug): array
	{
		return $this->createQueryBuilder('p')
			->leftJoin('p.tags', 't')
			->addSelect('t')
			->where('t.slug = :slug')
			->andWhere('p.isDeleted = false')
			->orderBy('p.createdAt', 'DESC')
			->setParameter('slug', $slug)
			->getQuery()
			->getResult();
	}
}
