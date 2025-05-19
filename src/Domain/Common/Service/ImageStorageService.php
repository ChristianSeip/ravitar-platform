<?php

namespace App\Domain\Common\Service;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validation;

class ImageStorageService {

	/**
	 * @param int   $maxSizeBytes
	 * @param array $allowedMimeTypes
	 */
	public function __construct(private readonly int $maxSizeBytes = 2_000_000, private readonly array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'])
	{
	}

	/**
	 * Stores a validated image file in the given directory.
	 *
	 * @param UploadedFile $file
	 * @param string       $dir
	 *
	 * @return string The generated filename
	 *
	 * @throws RuntimeException
	 */
	public function store(UploadedFile $file, string $dir): string
	{
		$this->validate($file);
		$filename = uniqid().'.'.$file->guessExtension();
		$file->move($dir, $filename);
		return $filename;
	}

	/**
	 * Validates the uploaded image against configured size and MIME type constraints.
	 *
	 * @param UploadedFile $file
	 *
	 * @throws RuntimeException
	 */
	private function validate(UploadedFile $file): void
	{
		$validator = Validation::createValidator();
		$violations = $validator->validate($file, new File([
				'maxSize' => $this->maxSizeBytes,
				'mimeTypes' => $this->allowedMimeTypes,
				'mimeTypesMessage' => 'Your uploaded file is not a valid file type.',
			])
		);

		if (count($violations) > 0) {
			throw new RuntimeException($violations[0]->getMessage());
		}
	}
}