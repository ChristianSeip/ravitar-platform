<?php

namespace App\Domain\Blog\Service;

class SearchParserService
{

	public function parse(string $input): string
	{
		$phrases = [];
		$required = [];
		$excluded = [];
		$optional = [];

		// Extract phrases in quotation marks
		preg_match_all('/"([^"]+)"/', $input, $matches);
		foreach ($matches[1] as $match) {
			$phrases[] = $this->sanitizePhrase($match);
			$input = str_replace("\"$match\"", '', $input);
		}

		// Break down remaining terms
		foreach (preg_split('/\s+/', trim($input)) as $token) {
			$token = trim($token);
			if ($token === '') {
				continue;
			}

			if (str_starts_with($token, '+')) {
				$required[] = $this->sanitizeWord(substr($token, 1));
			}
			else if (str_starts_with($token, '-')) {
				$excluded[] = $this->sanitizeWord(substr($token, 1));
			}
			else {
				$optional[] = $this->sanitizeWord($token);
			}
		}

		// build ts query
		$parts = [];

		foreach ($phrases as $p) {
			$parts[] = '"' . $p . '"';
		}
		foreach ($required as $r) {
			$parts[] = $r;
		}
		foreach ($optional as $o) {
			$parts[] = $o;
		}
		foreach ($excluded as $e) {
			$parts[] = '!' . $e;
		}

		return implode(' & ', array_filter($parts));
	}

	private function sanitizeWord(string $word): string
	{
		return preg_replace('/[^a-z0-9äöüß\-]+/iu', '', $word);
	}

	private function sanitizePhrase(string $phrase): string
	{
		$phrase = preg_replace('/[^a-z0-9äöüß\s\-]+/iu', '', $phrase);
		return str_replace(' ', ' <-> ', trim($phrase));
	}
}
