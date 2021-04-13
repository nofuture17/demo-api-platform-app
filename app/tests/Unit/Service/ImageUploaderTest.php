<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\FileDataInput;
use App\Entity\Image;
use App\Service\ImageFileStorageInterface;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImageUploaderTest extends KernelTestCase
{
    private const FILE_PATH = __DIR__.'/../../files/image.png';

    /**
     * @var EntityManagerInterface|MockObject
     */
    private EntityManagerInterface | MockObject $entityManager;
    /**
     * @var ImageFileStorageInterface|MockObject
     */
    private ImageFileStorageInterface | MockObject $fileStorage;
    /**
     * @var TranslatorInterface|MockObject
     */
    private TranslatorInterface | MockObject $translator;
    /**
     * @var Filesystem|MockObject
     */
    private Filesystem | MockObject $filesystem;
    /**
     * @var HttpClientInterface|MockObject
     */
    private HttpClientInterface | MockObject $httpClient;

    public function testHandleFile(): void
    {
        $file = new UploadedFile(self::FILE_PATH, random_bytes(2));
        $service = $this->createService();
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(Image::class));
        $this->entityManager->expects(self::once())
            ->method('flush');
        $this->fileStorage->expects(self::once())
            ->method('saveFile')
            ->with(self::isInstanceOf(Image::class), $file);
        self::assertNotNull($service->handleFile($file));
    }

    public function testHandleJsonWithUrl(): void
    {
        $file = new UploadedFile(self::FILE_PATH, $fileName = random_bytes(2));
        $imageURL = 'https://some.site/200.png';
        $service = $this->createService();
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(Image::class));
        $this->entityManager->expects(self::once())
            ->method('flush');
        $this->fileStorage->expects(self::once())
            ->method('saveFile')
            ->with(self::isInstanceOf(Image::class), $file);
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);
        $response->expects(self::once())
            ->method('getContent')
            ->willReturn($content = 'content');
        $this->httpClient->expects(self::once())
            ->method('request')
            ->with('GET', $imageURL)
            ->willReturn($response);
        $this->filesystem->expects(self::once())
            ->method('tempnam')
            ->with('/tmp', '')
            ->willReturn(self::FILE_PATH);
        $this->filesystem->expects(self::once())
            ->method('dumpFile')
            ->with(self::FILE_PATH, $content);
        self::assertNull($service->handleJson(new FileDataInput($fileName, $imageURL))->getError());
    }

    public function testHandleJsonWithBase64(): void
    {
        $file = new UploadedFile(self::FILE_PATH, $fileName = random_bytes(2));
        $base64 = 'Base64';
        $service = $this->createService();
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(Image::class));
        $this->entityManager->expects(self::once())
            ->method('flush');
        $this->fileStorage->expects(self::once())
            ->method('saveFile')
            ->with(self::isInstanceOf(Image::class), $file);
        $this->filesystem->expects(self::once())
            ->method('tempnam')
            ->with('/tmp', '')
            ->willReturn(self::FILE_PATH);
        $this->filesystem->expects(self::once())
            ->method('dumpFile')
            ->with(self::FILE_PATH, base64_decode($base64));
        self::assertNull($service->handleJson(new FileDataInput($fileName, $base64))->getError());
    }

    public function testHandleJsonWithErrors(): void
    {
        $service = $this->createService();
        $image404URL = 'https://some.site/404.png';
        $response404 = $this->createMock(ResponseInterface::class);
        $response404->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(404);
        $this->httpClient->expects(self::once())
            ->method('request')
            ->with('GET', $image404URL)
            ->willReturn($response404);
        self::assertEquals(
            'File name is required',
            $service->handleJson(new FileDataInput())->getError()
        );
        self::assertEquals(
            'File must have url or base64 content',
            $service->handleJson(new FileDataInput('name'))->getError()
        );
        self::assertEquals(
            'Wrong file content',
            $service->handleJson(new FileDataInput('name', 'a'))->getError()
        );
        self::assertEquals(
            'Unable to get image by URL',
            $service->handleJson(new FileDataInput('name', $image404URL))->getError()
        );
        $service = $this->createService();
        $image500URL = 'https://some.site/500.png';
        $this->httpClient->expects(self::once())
            ->method('request')
            ->with('GET', $image500URL)
            ->willThrowException($this->createMock(ServerExceptionInterface::class));
        self::assertEquals(
            'Unable to get image by URL',
            $service->handleJson(new FileDataInput('name', $image500URL))->getError()
        );
    }

    private function createService(): ImageUploader
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->fileStorage = $this->createMock(ImageFileStorageInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(static function () {
                return current(func_get_args());
            });
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);

        return new ImageUploader(
            $this->entityManager,
            $this->fileStorage,
            $this->translator,
            $this->filesystem,
            $this->httpClient
        );
    }
}
