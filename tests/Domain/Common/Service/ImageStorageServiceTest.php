<?php

namespace App\Tests\Domain\Common\Service;

use App\Domain\Common\Service\ImageStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use RuntimeException;

class ImageStorageServiceTest extends TestCase
{
	private string $uploadDir;

	protected function setUp(): void
	{
		$this->uploadDir = sys_get_temp_dir() . '/image_test_uploads';
		if (!is_dir($this->uploadDir)) {
			mkdir($this->uploadDir, 0777, true);
		}
		array_map('unlink', glob($this->uploadDir . '/*'));
	}

	public function testStoreValidImage(): void
	{
		$file = $this->createValidUploadedJpeg('test.jpg');
		$service = new ImageStorageService();
		$filename = $service->store($file, $this->uploadDir);
		$this->assertFileExists($this->uploadDir . '/' . $filename);
		$this->assertStringEndsWith('.jpg', $filename);
	}

	public function testStoreFailsWithInvalidMimeType(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Your uploaded file is not a valid file type.');
		$file = $this->createPlainTextFile('test.txt');
		$service = new ImageStorageService();
		$service->store($file, $this->uploadDir);
	}

	public function testStoreFailsWhenFileTooLarge(): void
	{
		$this->expectException(RuntimeException::class);
		$file = $this->createLargeValidJpeg('big.jpg', 5_000_000);
		$service = new ImageStorageService(2_000_000); // 2 MB Limit
		$service->store($file, $this->uploadDir);
	}

	private function createValidUploadedJpeg(string $originalName): UploadedFile
	{
		$path = tempnam(sys_get_temp_dir(), 'img_');
		$jpegBinary = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCABkAGQDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAb/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGvAP/Z');
		file_put_contents($path, $jpegBinary);
		return new UploadedFile($path, $originalName, 'image/jpeg', null, true);
	}

	private function createPlainTextFile(string $originalName): UploadedFile
	{
		$path = tempnam(sys_get_temp_dir(), 'txt_');
		file_put_contents($path, 'This is not an image.');
		return new UploadedFile($path, $originalName, 'text/plain', null, true);
	}

	private function createLargeValidJpeg(string $originalName, int $minSize): UploadedFile
	{
		$path = tempnam(sys_get_temp_dir(), 'img_large_');
		$jpegBinary = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCABkAGQDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAb/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGvAP/Z');
		$repeats = (int) ceil($minSize / strlen($jpegBinary));
		file_put_contents($path, str_repeat($jpegBinary, $repeats));
		return new UploadedFile($path, $originalName, 'image/jpeg', null, true);
	}
}
