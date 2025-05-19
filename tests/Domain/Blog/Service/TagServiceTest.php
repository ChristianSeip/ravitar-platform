<?php


namespace App\Tests\Domain\Blog\Service;

use App\Domain\Blog\Entity\Tag;
use App\Domain\Blog\Repository\TagRepository;
use App\Domain\Blog\Service\TagService;
use App\Domain\Common\Service\SlugService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
	private TagService $service;
	private SlugService $slugService;
	private EntityManagerInterface $em;
	private TagRepository $tagRepo;

	protected function setUp(): void
	{
		$this->slugService = $this->createMock(SlugService::class);
		$this->slugService->method('generateSlug')->willReturnCallback(fn($str) => strtolower(trim($str)));

		$this->em = $this->createMock(EntityManagerInterface::class);
		$this->em->expects($this->any())->method('persist');

		$this->tagRepo = $this->createMock(TagRepository::class);
		$this->service = new TagService($this->tagRepo, $this->em, $this->slugService);
	}

	public function testParseTagInput(): void
	{
		$input = " PHP , Go, , JavaScript , php ";
		$expected = ['PHP', 'Go', 'JavaScript'];
		$method = new \ReflectionMethod($this->service, 'parseTagInput');
		$method->setAccessible(true);

		$result = $method->invoke($this->service, $input);
		$this->assertEqualsCanonicalizing($expected, $result);
	}

	public function testGetExistingTagsBySlugs(): void
	{
		$tag = new Tag();
		$tag->setName('PHP');
		$tag->setSlug('php');

		$this->tagRepo
			->expects($this->once())
			->method('findBy')
			->with(['slug' => ['php']])
			->willReturn([$tag]);

		$method = new \ReflectionMethod($this->service, 'getExistingTagsBySlugs');
		$method->setAccessible(true);
		$result = $method->invoke($this->service, ['php']);

		$this->assertArrayHasKey('php', $result);
		$this->assertSame($tag, $result['php']);
	}

	public function testBuildTagListCreatesNewTags(): void
	{
		$names = ['PHP', 'Go'];
		$existing = ['php' => $this->makeTag('PHP', 'php')];

		$method = new \ReflectionMethod($this->service, 'buildTagList');
		$method->setAccessible(true);
		$result = $method->invoke($this->service, $names, $existing);

		$this->assertCount(2, $result);
		$this->assertEquals('php', $result[0]->getSlug());
		$this->assertEquals('go', $result[1]->getSlug());
	}

	public function testProcessTagInput(): void
	{
		$tag = $this->makeTag('PHP', 'php');

		$this->tagRepo
			->method('findBy')
			->willReturn([$tag]);

		$result = $this->service->processTagInput("PHP, Go");

		$this->assertCount(2, $result);
		$this->assertEquals('php', $result[0]->getSlug());
		$this->assertEquals('go', $result[1]->getSlug());
	}

	private function makeTag(string $name, string $slug): Tag
	{
		$tag = new Tag();
		$tag->setName($name);
		$tag->setSlug($slug);
		return $tag;
	}
}
