<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}

	public function countByTagSlug(string $slug): int
	{
		return (int) $this->createTagQueryBuilder($slug)
			->select('COUNT(p.id)')
			->getQuery()
			->getSingleScalarResult();
	}

	public function findByTagSlug(string $slug): array
	{
		return $this->createTagQueryBuilder($slug)
			->addSelect('t')
			->orderBy('p.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}

	public function findByTagSlugPaginated(string $slug, int $limit, int $offset): array
	{
		return $this->createTagQueryBuilder($slug)
			->addSelect('t')
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult();
	}

	private function createTagQueryBuilder(string $slug)
	{
		return $this->createQueryBuilder('p')
			->innerJoin('p.tags', 't')
			->where('t.slug = :slug')
			->andWhere('p.isDeleted = false')
			->setParameter('slug', $slug);
	}

	public function countByTsQuery(string $tsQuery): int
	{
		$conn = $this->getEntityManager()->getConnection();

		$sql = <<<SQL
        SELECT COUNT(*) FROM blog_post
        WHERE is_deleted = false
          AND to_tsvector('german', title || ' ' || content)
          @@ to_tsquery('german', :query)
        SQL;

		return (int) $conn->prepare($sql)
			->executeQuery(['query' => $tsQuery])
			->fetchOne();
	}

	public function findByTsQuery(string $tsQuery, int $limit, int $offset): array
	{
		$conn = $this->getEntityManager()->getConnection();

		$sql = <<<SQL
        SELECT id FROM blog_post
        WHERE is_deleted = false
          AND to_tsvector('german', title || ' ' || content)
          @@ to_tsquery('german', :query)
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
        SQL;

		$ids = $conn->prepare($sql)->executeQuery([
			'query'  => $tsQuery,
			'limit'  => $limit,
			'offset' => $offset,
		])->fetchFirstColumn();

		return $ids ? $this->findBy(['id' => $ids], ['createdAt' => 'DESC']) : [];
	}

	public function findLatest(int $limit): array
	{
		$qb = $this->createQueryBuilder('p');
		return $qb
			->select('p')
			->where('p.isDeleted = false')
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit)
			->getQuery()
			->setFetchMode(Post::class, 'tags', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY)
			->getResult();
	}

	public function countAll(): int
	{
		return (int) $this->createQueryBuilder('p')
			->select('COUNT(p.id)')
			->where('p.isDeleted = false')
			->andWhere('p.createdAt <= :now')
			->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
			->getQuery()
			->getSingleScalarResult();
	}

	public function findAllPaginated(int $limit, int $offset): array
	{
		return $this->createQueryBuilder('p')
			->where('p.isDeleted = false')
			->andWhere('p.createdAt <= :now')
			->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult();
	}
}
