<?php

namespace App\Tests\Domain\Blog\Service;

use App\Domain\Blog\Service\SearchParserService;
use PHPUnit\Framework\TestCase;

class SearchParserServiceTest extends TestCase
{
	private SearchParserService $parser;

	protected function setUp(): void
	{
		$this->parser = new SearchParserService();
	}

	public function testSimpleWord(): void
	{
		$result = $this->parser->parse('test');
		$this->assertEquals('test', $result);
	}

	public function testMultipleWords(): void
	{
		$result = $this->parser->parse('foo bar');
		$this->assertEquals('foo & bar', $result);
	}

	public function testRequiredWords(): void
	{
		$result = $this->parser->parse('+foo +bar');
		$this->assertEquals('foo & bar', $result);
	}

	public function testExcludedWord(): void
	{
		$result = $this->parser->parse('foo -bar');
		$this->assertEquals('foo & !bar', $result);
	}

	public function testExactPhrase(): void
	{
		$result = $this->parser->parse('"foo bar"');
		$this->assertEquals('"foo <-> bar"', $result);
	}

	public function testMixedComplexInput(): void
	{
		$result = $this->parser->parse('"foo bar" +baz -qux lorem');
		$this->assertEquals('"foo <-> bar" & baz & lorem & !qux', $result);
	}

	public function testIgnoresSpecialCharacters(): void
	{
		$result = $this->parser->parse('foo! @bar #baz');
		$this->assertEquals('foo & bar & baz', $result);
	}

	public function testStripsDoubleQuotes(): void
	{
		$result = $this->parser->parse('"foo bar" "baz qux"');
		$this->assertEquals('"foo <-> bar" & "baz <-> qux"', $result);
	}

	public function testEmptyQuery(): void
	{
		$result = $this->parser->parse('   ');
		$this->assertEquals('', $result);
	}
}