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

	public function findByTagSlugPaginated(string $slug, int $limit, int $offset): array
	{
		return $this->createQueryBuilder('p')
			->leftJoin('p.tags', 't')
			->addSelect('t')
			->where('t.slug = :slug')
			->andWhere('p.isDeleted = false')
			->orderBy('p.createdAt', 'DESC')
			->setParameter('slug', $slug)
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult();
	}

	public function countByTagSlug(string $slug): int
	{
		return (int) $this->createQueryBuilder('p')
			->select('COUNT(p.id)')
			->leftJoin('p.tags', 't')
			->where('t.slug = :slug')
			->andWhere('p.isDeleted = false')
			->setParameter('slug', $slug)
			->getQuery()
			->getSingleScalarResult();
	}
}
