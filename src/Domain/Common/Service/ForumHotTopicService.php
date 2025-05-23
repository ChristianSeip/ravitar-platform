<?php

namespace App\Domain\Common\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ForumHotTopicService {

	private HttpClientInterface $xenforoClient;

	public function __construct(HttpClientInterface $xenforoClient, private readonly CacheInterface $cache)
	{
		$this->xenforoClient = $xenforoClient;
	}

	/**
	 * Returns a list of the hottest forum topics based on reply count and recency.
	 *
	 * @param int $limit
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	public function getHotTopics(int $limit = 5): array
	{
		return $this->cache->get("hot_topics_$limit", function (ItemInterface $item) use ($limit) {
			$item->expiresAfter(900);

			$threads = $this->fetchThreads();
			$scored = $this->calculateHotness($threads);

			return array_slice($scored, 0, $limit);
		});
	}

	/**
	 * Fetches the latest threads from the XenForo API.
	 * Threads are sorted by last post date in descending order.
	 *
	 * @return array
	 */
	private function fetchThreads(): array
	{
		$response = $this->xenforoClient->request('GET', 'threads', [
			'query' => [
				'limit' => 50,
				'order' => 'last_post_date',
				'direction' => 'desc',
			],
		]);

		$data = $response->toArray();
		return $data['threads'] ?? [];
	}

	/**
	 * Calculates a "hotness" score for each thread based on replies and freshness.
	 *
	 * Formula: score = (replies * 3) - (age in hours * 0.5)
	 *
	 * @param array $threads
	 *
	 * @return array
	 */
	private function calculateHotness(array $threads): array
	{
		$now = time();

		$scored = array_map(function ($thread) use ($now) {
			$ageInHours = ($now - $thread['last_post_date']) / 3600;
			$score = ($thread['reply_count'] * 3) - ($ageInHours * 0.5);

			return [
				'title' => $thread['title'],
				'score' => $score,
				'url' => $thread['view_url'] ?? null,
				'replies' => $thread['reply_count'],
				'lastPostDate' => $thread['last_post_date'],
			];
		}, $threads);

		usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
		return $scored;
	}

}