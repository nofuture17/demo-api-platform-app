<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Image;
use App\Entity\ImageFile;
use App\Exception\LogicException;
use App\Service\ImageFileStorage;
use App\Service\ImageManipulatorInterface;
use App\Tests\Helper;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImageFileStorageTest extends KernelTestCase
{
    private const THUMB_SIZE_SMALL = 100;
    private const THUMB_SIZE_BIG = 400;
    private const THUMB_SIZE_SUFFIX_SMALL = '_'.self::THUMB_SIZE_SMALL.'px';
    private const THUMB_SIZE_SUFFIX_BIG = '_'.self::THUMB_SIZE_BIG.'px';
    private const IMAGE_PATH = __DIR__.'/../../files/image.png';
    private const DATE_FORMAT = 'Y-m-d';

    /**
     * @var TranslatorInterface|MockObject
     */
    private TranslatorInterface | MockObject $translator;
    /**
     * @var Filesystem|MockObject
     */
    private Filesystem | MockObject $filesystem;
    /**
     * @var ImageManipulatorInterface|MockObject
     */
    private ImageManipulatorInterface | MockObject $imageManipulator;

    private static function assertImage(Image $result, string $name, string $ext): void
    {
        self::assertEquals($name, $result->getName());
        self::assertEquals($ext, $result->getExtension());
        self::assertEquals(date(self::DATE_FORMAT), $result->getCreatedAt()->format(self::DATE_FORMAT));
    }

    private static function assertFailedImage(Image $result, string $name, string $ext, string $error): void
    {
        self::assertImage($result, $name, $ext);
        self::assertCount(0, $result->getFiles());
        self::assertEquals($error, $result->getError());
    }

    public function testRemoveImageFiles(): void
    {
        $image = (new Image())->setId(Helper::generateImageId());
        $service = $this->createService($basePath = random_bytes(10));
        $this->filesystem->expects(self::once())
            ->method('remove')
            ->with([
                $imageBase = $this->getImagePath($basePath, $image),
                $imageBase.self::THUMB_SIZE_SUFFIX_SMALL,
                $imageBase.self::THUMB_SIZE_SUFFIX_BIG,
            ]);
        $service->removeImageFiles($image);
    }

    public function testGetFilePath(): void
    {
        $image = (new Image())->setId(Helper::generateImageId());
        $imageWithoutFile = (new Image())->setId(Helper::generateImageId());
        $service = $this->createService($basePath = random_bytes(2));
        $exception = null;
        try {
            $service->getFilePath($image, 'unexpected');
        } catch (LogicException $exception) {
        }
        self::assertEquals($exception, new LogicException('Unexpected thumb variant'));
        $this->filesystem->expects(self::exactly(4))
            ->method('exists')
            ->withConsecutive(
                [$imageBase = $this->getImagePath($basePath, $image)],
                [$imageBig = $imageBase.self::THUMB_SIZE_SUFFIX_BIG],
                [$imageSmall = $imageBase.self::THUMB_SIZE_SUFFIX_SMALL],
                [$this->getImagePath($basePath, $imageWithoutFile)]
            )
            ->willReturnOnConsecutiveCalls(true, true, true, false);
        self::assertEquals($imageBase, $service->getFilePath($image, null));
        self::assertEquals($imageBig, $service->getFilePath($image, ImageFile::TYPE_THUMB_BIG));
        self::assertEquals($imageSmall, $service->getFilePath($image, ImageFile::TYPE_THUMB_SMALL));
        try {
            $service->getFilePath($imageWithoutFile, null);
        } catch (LogicException $exception) {
        }
        self::assertEquals($exception, new LogicException('File not found'));
    }

    public function testSaveFileWithErrors(): void
    {
        $imageEntity = (new Image())->setId(Helper::generateImageId());
        $service = $this->createService($basePath = random_bytes(2));
        $image = $this->createMock(ImageInterface::class);
        $image->method('getSize')->willReturn(new Box(1, 1));
        $saveImageCallsCount = 0;
        $this->imageManipulator->expects(self::exactly(3))
            ->method('saveImage')
            ->willReturnCallback(static function () use (&$saveImageCallsCount, $image) {
                if (0 === $saveImageCallsCount++) {
                    throw new RuntimeException();
                }

                return $image;
            });
        $createThumbCallsCount = 0;
        $this->imageManipulator->expects(self::exactly(3))
            ->method('createThumb')
            ->willReturnCallback(static function () use (&$createThumbCallsCount, $image) {
                if (in_array(++$createThumbCallsCount, [1, 3])) {
                    throw new RuntimeException();
                }

                return $image;
            });
        $this->filesystem->expects(self::exactly(3))
            ->method('remove')
            ->with([
                $this->getImagePath($basePath, $imageEntity),
                $this->getImagePath($basePath, $imageEntity).self::THUMB_SIZE_SUFFIX_SMALL,
                $this->getImagePath($basePath, $imageEntity).self::THUMB_SIZE_SUFFIX_BIG,
            ]);
        $service->saveFile($imageEntity, new UploadedFile(
            self::IMAGE_PATH,
            ($name = 'name').'.'.($ext = 'ext')
        ));
        self::assertFailedImage($imageEntity, $name, $ext, 'Unable to resize image');
        $service->saveFile($imageEntity, new UploadedFile(
            self::IMAGE_PATH,
            ($name = 'name').'.'.($ext = 'ext')
        ));
        self::assertFailedImage($imageEntity, $name, $ext, 'Unable to resize image');
        $service->saveFile($imageEntity, new UploadedFile(
            self::IMAGE_PATH,
            ($name = 'name').'.'.($ext = 'ext')
        ));
        self::assertFailedImage($imageEntity, $name, $ext, 'Unable to resize image');
    }

    public function testSaveFile(): void
    {
        $file = new UploadedFile(
            self::IMAGE_PATH,
            ($name = 'name').'.'.($ext = 'ext')
        );
        $imageEntity = (new Image())->setId(Helper::generateImageId());
        $service = $this->createService($basePath = random_bytes(2));
        $image = $this->createMock(ImageInterface::class);
        $image->method('getSize')
            ->willReturn($imageSize = new Box(random_int(1, 1000), random_int(1, 1000)));
        $smallThumbImage = $this->createMock(ImageInterface::class);
        $smallThumbImage->method('getSize')
            ->willReturn($smallThumbImageSize = new Box(random_int(1, 1000), random_int(1, 1000)));
        $bigThumbImage = $this->createMock(ImageInterface::class);
        $bigThumbImage->method('getSize')
            ->willReturn($bigThumbImageSize = new Box(random_int(1, 1000), random_int(1, 1000)));
        $savePath = $this->getImagePath($basePath, $imageEntity);
        $this->imageManipulator->expects(self::once())
            ->method('saveImage')
            ->with(self::IMAGE_PATH, $savePath)
            ->willReturn($image);
        $this->imageManipulator->expects(self::exactly(2))
            ->method('createThumb')
            ->withConsecutive([
                $image,
                $savePath.self::THUMB_SIZE_SUFFIX_SMALL,
                self::THUMB_SIZE_SMALL,
                self::THUMB_SIZE_SMALL,
                false,
                $file->guessExtension(),
            ], [
                $image,
                $savePath.self::THUMB_SIZE_SUFFIX_BIG,
                self::THUMB_SIZE_BIG,
                self::THUMB_SIZE_BIG,
                true,
                $file->guessExtension(),
            ])
            ->willReturnOnConsecutiveCalls($smallThumbImage, $bigThumbImage);
        $service->saveFile($imageEntity, $file);
        self::assertImage($imageEntity, $name, $ext);
        self::assertCount(3, $imageEntity->getFiles());
        self::assertEquals($imageSize->getWidth(), $imageEntity->getFiles()->get(0)->getWidth());
        self::assertEquals($imageSize->getHeight(), $imageEntity->getFiles()->get(0)->getHeight());
        self::assertEquals(ImageFile::TYPE_ORIGINAL, $imageEntity->getFiles()->get(0)->getType());
        self::assertEquals($smallThumbImageSize->getWidth(), $imageEntity->getFiles()->get(1)->getWidth());
        self::assertEquals($smallThumbImageSize->getHeight(), $imageEntity->getFiles()->get(1)->getHeight());
        self::assertEquals(ImageFile::TYPE_THUMB_SMALL, $imageEntity->getFiles()->get(1)->getType());
        self::assertEquals($bigThumbImageSize->getWidth(), $imageEntity->getFiles()->get(2)->getWidth());
        self::assertEquals($bigThumbImageSize->getHeight(), $imageEntity->getFiles()->get(2)->getHeight());
        self::assertEquals(ImageFile::TYPE_THUMB_BIG, $imageEntity->getFiles()->get(2)->getType());
    }

    private function createService(string $basePath): ImageFileStorage
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(static function () {
                return current(func_get_args());
            });
        $this->imageManipulator = $this->createMock(ImageManipulatorInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        return new ImageFileStorage($this->translator, $this->filesystem, $this->imageManipulator, $basePath);
    }

    public function getImagePath(string $basePath, Image $image): string
    {
        return "{$basePath}/{$image->getCreatedAt()->format(self::DATE_FORMAT)}/{$image->getId()}";
    }
}
