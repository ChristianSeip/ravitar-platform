<?php

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Entity\Comment;
use App\Domain\Blog\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Comment::class);
	}

	public function findCommentsByPostStructured(Post $post): array
	{
		$all = $this->createQueryBuilder('c')
			->where('c.post = :post')
			->orderBy('c.createdAt', 'ASC')
			->setParameter('post', $post)
			->getQuery()
			->getResult();

		$root = [];
		$children = [];

		foreach ($all as $comment) {
			if ($comment->getParent() === null) {
				$root[] = $comment;
			}
			else {
				$parentId = $comment->getParent()->getId();
				$children[$parentId][] = $comment;
			}
		}

		return [
			'root' => $root,
			'children' => $children,
		];
	}
}
