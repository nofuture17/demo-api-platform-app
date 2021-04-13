<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\UploadFileController;
use App\Entity\Image;
use App\Service\ImageUploaderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UploadFileControllerTest extends KernelTestCase
{
    private const IMAGE_PATH = __DIR__.'/../../files/image.png';
    /**
     * @var ImageUploaderInterface|MockObject
     */
    protected ImageUploaderInterface | MockObject $imageUploader;
    /**
     * @var TranslatorInterface|MockObject
     */
    protected TranslatorInterface | MockObject $translator;

    public function testInvokeWithError(): void
    {
        $controller = $this->createController();
        $exception = null;
        try {
            $controller->__invoke(new Request());
        } catch (BadRequestException $exception) {
        }
        self::assertEquals('Unexpected request format', $exception->getMessage());
    }

    public function testInvoke(): void
    {
        $images = [];
        $files = [
            new UploadedFile(self::IMAGE_PATH, random_bytes(2)),
            new UploadedFile(self::IMAGE_PATH, random_bytes(2)),
        ];
        foreach ($files as $_) {
            $images[] = new Image();
        }
        $controller = $this->createController();
        $this->imageUploader->expects(self::exactly(count($files)))
            ->method('handleFile')
            ->withConsecutive(...array_map(static fn (UploadedFile $file) => [$file], $files))
            ->willReturnOnConsecutiveCalls(...$images);
        self::assertEquals(
            new ArrayCollection($images),
            $controller->__invoke(new Request(files: [Image::FILES_REQUEST_PROPERTY => $files]))
        );
    }

    private function createController(): UploadFileController
    {
        $this->imageUploader = $this->createMock(ImageUploaderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(static function () {
                return current(func_get_args());
            });

        return new UploadFileController($this->imageUploader, $this->translator);
    }
}
