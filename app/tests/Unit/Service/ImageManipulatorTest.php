<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ImageManipulator;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ImageManipulatorTest extends KernelTestCase
{
    private Filesystem | MockObject $filesystem;
    private ImagineInterface | MockObject $imagine;

    public function testSaveImage(): void
    {
        $filePath = random_bytes(2);
        $savePath = random_bytes(2);
        $service = $this->createService();
        $image = $this->createMock(ImageInterface::class);
        $this->imagine->expects(self::once())
            ->method('open')
            ->with($filePath)
            ->willReturn($image);
        $this->filesystem->expects(self::once())
            ->method('copy')
            ->with($filePath, $savePath);
        $this->filesystem->expects(self::once())
            ->method('remove')
            ->with($filePath);
        self::assertSame($image, $service->saveImage($filePath, $savePath));
    }

    public function testCreateRatioThumb(): void
    {
        $savePath = random_bytes(2);
        $width = random_int(1, 1000);
        $height = random_int(1, 1000);
        $format = random_bytes(2);
        $service = $this->createService();
        $thumb = $this->createMock(ImageInterface::class);
        $thumb->expects(self::once())
            ->method('save')
            ->with($savePath, ['format' => $format]);
        $image = $this->createMock(ImageInterface::class);
        $image->expects(self::once())
            ->method('thumbnail')
            ->with(new Box($width, $height), ManipulatorInterface::THUMBNAIL_INSET)
            ->willReturn($thumb);

        self::assertSame($thumb, $service->createThumb($image, $savePath, $width, $height, true, $format));
    }

    /**
     * @throws \Exception
     */
    public function testRatioThumb(): void
    {
        $savePath = random_bytes(2);
        $width = random_int(1, 1000);
        $height = random_int(1, 1000);
        $format = random_bytes(2);
        $service = $this->createService();
        $thumb = $this->createMock(ImageInterface::class);
        $thumb->expects(self::once())
            ->method('save')
            ->with($savePath, ['format' => $format]);
        $copy = $this->createMock(ImageInterface::class);
        $copy->expects(self::once())
            ->method('resize')
            ->with(new Box($width, $height))
            ->willReturn($thumb);
        $image = $this->createMock(ImageInterface::class);
        $image->expects(self::once())
            ->method('copy')
            ->willReturn($copy);

        self::assertSame($thumb, $service->createThumb($image, $savePath, $width, $height, false, $format));
    }

    private function createService(): ImageManipulator
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->imagine = $this->createMock(ImagineInterface::class);

        return new ImageManipulator($this->filesystem, $this->imagine);
    }
}
