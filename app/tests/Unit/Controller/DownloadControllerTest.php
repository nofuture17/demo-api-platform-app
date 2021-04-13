<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\DownloadController;
use App\Entity\Image;
use App\Service\ImageFileStorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DownloadControllerTest extends KernelTestCase
{
    private const IMAGE_PATH = __DIR__.'/../../files/image.png';
    /**
     * @var ImageFileStorageInterface|MockObject
     */
    private ImageFileStorageInterface | MockObject $fileStorage;
    /**
     * @var TranslatorInterface|MockObject
     */
    private TranslatorInterface | MockObject $translator;

    public function testInvokeWithError(): void
    {
        $controller = $this->createController();
        $exception = null;
        try {
            $controller->__invoke((new Image())->setError('error'), new Request());
        } catch (BadRequestException $exception) {
        }
        self::assertEquals('Image has uploading error', $exception->getMessage());
    }

    public function testInvoke(): void
    {
        $image = (new Image())->setName($name = 'name');
        $request = new Request([Image::THUMB_REQUEST_PROPERTY => $thumb = random_bytes(2)]);
        $controller = $this->createController();
        $this->fileStorage->expects(self::once())
            ->method('getFilePath')
            ->with($image, $thumb)
            ->willReturn(self::IMAGE_PATH);
        $response = $controller->__invoke($image, $request);
        self::assertEquals(self::IMAGE_PATH, $response->getFile()->getPathname());
        self::assertEquals("attachment; filename={$name}", $response->headers->get('Content-Disposition'));
    }

    private function createController(): DownloadController
    {
        $this->fileStorage = $this->createMock(ImageFileStorageInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(static function () {
                return current(func_get_args());
            });

        return new DownloadController($this->fileStorage, $this->translator);
    }
}
