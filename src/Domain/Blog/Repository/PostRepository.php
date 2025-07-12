<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
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
	 * @param bool $publishedOnly
	 *
	 * @return Post[]
	 */
	public function findByTagSlugPaginated(string $slug, int $limit, int $offset, bool $publishedOnly = true): array
	{
		$qb = $this->createTagQueryBuilder($slug)
			->addSelect('t')
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return $qb->getQuery()->getResult();
	}

	/**
	 * Builds a base query for posts matching a specific tag slug.
	 *
	 * @param string $slug
	 *
	 * @return QueryBuilder
	 */
	private function createTagQueryBuilder(string $slug)
	{
		return $this->createQueryBuilder('p')
			->innerJoin('p.tags', 't')
			->where('t.slug = :slug')
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
        	AND created_at <= NOW()
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
        	AND created_at <= NOW()
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
	 * @param bool $publishedOnly
	 *
	 * @return Post[]
	 */
	public function findLatest(int $limit, bool $publishedOnly = true): array
	{
		$qb = $this->createQueryBuilder('p')
			->select('p')
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit);
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return $qb->getQuery()
			->setFetchMode(Post::class, 'tags', ClassMetadata::FETCH_EXTRA_LAZY)
			->getResult();
	}

	/**
	 * Returns the total number of non-deleted, published posts.
	 *
	 * @param bool $publishedOnly
	 *
	 * @return int
	 */
	public function countAll(bool $publishedOnly = true): int
	{
		$qb = $this->createQueryBuilder('p')->select('COUNT(p.id)');
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return (int) $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * Returns a paginated list of all published posts.
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param bool $publishedOnly
	 *
	 * @return Post[]
	 */
	public function findAllPaginated(int $limit, int $offset, bool $publishedOnly = true): array
	{
		$qb = $this->createQueryBuilder('p')
			->orderBy('p.createdAt', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return $qb->getQuery()->getResult();
	}

	/**
	 * Returns all published and non-deleted posts.
	 *
	 * @param bool $publishedOnly
	 *
	 * @return Post[]
	 */
	public function findAllPublished(bool $publishedOnly = true): array
	{
		$qb = $this->createQueryBuilder('p')->orderBy('p.createdAt', 'DESC');
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return $qb->getQuery()->getResult();
	}

	/**
	 * Returns the previous and next post relative to the given post (based on ID).
	 *
	 * @param Post $post
	 *
	 * @return array{previous: Post|null, next: Post|null}
	 */
	public function findPostNeighbors(Post $post): array
	{
		$previousQb = $this->createQueryBuilder('p')
			->where('p.id < :id')
			->setParameter('id', $post->getId())
			->orderBy('p.id', 'DESC')
			->setMaxResults(1);
		$previousQb = $this->applyPublicationFilters($previousQb, true);
		$previous = $previousQb->getQuery()->getOneOrNullResult();

		$nextQb = $this->createQueryBuilder('p')
			->where('p.id > :id')
			->setParameter('id', $post->getId())
			->orderBy('p.id', 'ASC')
			->setMaxResults(1);
		$nextQb = $this->applyPublicationFilters($nextQb, true);
		$next = $nextQb->getQuery()->getOneOrNullResult();

		return [
			'previous' => $previous,
			'next' => $next,
		];
	}

	private function applyPublicationFilters(QueryBuilder $qb, bool $publishedOnly = true): QueryBuilder
	{
		if ($publishedOnly) {
			$qb->andWhere('p.isDeleted = false')
				->andWhere('p.createdAt <= :now')
				->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE);
		}
		return $qb;
	}

	public function findOneBySlug(string $slug, bool $publishedOnly = true): ?Post
	{
		$qb = $this->createQueryBuilder('p')
			->where('p.slug = :slug')
			->setParameter('slug', $slug)
			->setMaxResults(1);
		$qb = $this->applyPublicationFilters($qb, $publishedOnly);
		return $qb->getQuery()->getOneOrNullResult();
	}
}
