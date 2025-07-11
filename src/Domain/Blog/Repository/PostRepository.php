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

	/**
	 * Returns the total number of posts associated with a given tag slug.
	 *
	 * @param string $slug
	 *
	 * @return int
	 */
	public function countByTagSlug(string $slug): int
	{
		return (int) $this->createTagQueryBuilder($slug)
			->select('COUNT(p.id)')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Returns all posts associated with a given tag slug.
	 *
	 * @param string $slug
	 *
	 * @return Post[]
	 */
	public function findByTagSlug(string $slug): array
	{
		return $this->createTagQueryBuilder($slug)
			->addSelect('t')
			->orderBy('p.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}

	/**
	 * Returns a paginated list of posts for a given tag slug.
	 *
	 * @param string $slug
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Post[]
	 */
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

	/**
	 * Builds a base query for posts matching a specific tag slug.
	 *
	 * @param string $slug
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function createTagQueryBuilder(string $slug)
	{
		return $this->createQueryBuilder('p')
			->innerJoin('p.tags', 't')
			->where('t.slug = :slug')
			->andWhere('p.isDeleted = false')
			->setParameter('slug', $slug);
	}

	/**
	 * Returns the number of posts matching a PostgreSQL fulltext ts_query.
	 *
	 * @param string $tsQuery
	 *
	 * @return int
	 */
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

	/**
	 * Returns a paginated list of posts matching a fulltext search query.
	 *
	 * @param string $tsQuery
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Post[]
	 */
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

	/**
	 * Returns the latest published posts, limited by a given number.
	 *
	 * @param int $limit
	 *
	 * @return Post[]
	 */
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

	/**
	 * Returns the total number of non-deleted, published posts.
	 *
	 * @return int
	 */
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

	/**
	 * Returns a paginated list of all published posts.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Post[]
	 */
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

	/**
	 * Returns all published and non-deleted posts.
	 *
	 * @return Post[]
	 */
	public function findAllPublished(): array
	{
		return $this->createQueryBuilder('p')
			->where('p.isDeleted = false')
			->andWhere('p.createdAt <= :now')
			->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
			->orderBy('p.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}

	/**
	 * Finds the previous and next post relative to the given post (based on ID).
	 *
	 * @param Post $post
	 *
	 * @return array{previous: Post|null, next: Post|null}
	 */
	public function findPostNeighbors(Post $post): array
	{
		$qb = $this->createQueryBuilder('p')
			->andWhere('p.id != :id')
			->setParameter('id', $post->getId())
			->andWhere('p.id < :id OR p.id > :id')
			->orderBy('p.id', 'ASC');

		$results = $qb->getQuery()->getResult();

		$previous = null;
		$next = null;

		foreach ($results as $candidate) {
			if ($candidate->getId() < $post->getId()) {
				$previous = $candidate;
			}
			elseif ($candidate->getId() > $post->getId()) {
				$next = $candidate;
				break;
			}
		}

		return [
			'previous' => $previous,
			'next' => $next,
		];
	}
}
