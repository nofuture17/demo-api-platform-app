<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use App\Entity\ImageFile;
use App\Exception\LogicException;
use Imagine\Exception\Exception;
use Imagine\Image\ImageInterface;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImageFileStorage implements ImageFileStorageInterface
{
    private const THUMB_SIZE_SMALL = 100;
    private const THUMB_SIZE_BIG = 400;
    private const THUMB_SIZE_SUFFIX_SMALL = '_'.self::THUMB_SIZE_SMALL.'px';
    private const THUMB_SIZE_SUFFIX_BIG = '_'.self::THUMB_SIZE_BIG.'px';

    public function __construct(
        private TranslatorInterface $translator,
        private Filesystem $filesystem,
        private ImageManipulatorInterface $imageManipulator,
        private string $basePath
    ) {
    }

    #[Pure]
    public function getFilePath(Image $image, ?string $thumb): string
    {
        $path = $this->getImageFilePath($image);
        if (ImageFile::TYPE_THUMB_BIG === $thumb) {
            $path .= self::THUMB_SIZE_SUFFIX_BIG;
        } elseif (ImageFile::TYPE_THUMB_SMALL === $thumb) {
            $path .= self::THUMB_SIZE_SUFFIX_SMALL;
        } elseif (null !== $thumb) {
            throw new LogicException($this->translator->trans('Unexpected thumb variant'));
        }

        if (!$this->filesystem->exists($path)) {
            throw new LogicException($this->translator->trans('File not found'));
        }

        return $path;
    }

    public function removeImageFiles(Image $image): void
    {
        $this->filesystem->remove([
            $path = $this->getImageFilePath($image),
            $path.self::THUMB_SIZE_SUFFIX_SMALL,
            $path.self::THUMB_SIZE_SUFFIX_BIG,
        ]);
    }

    public function saveFile(Image $imageEntity, UploadedFile $file): void
    {
        $pathInfo = pathinfo($file->getClientOriginalName());
        $imageEntity->setName($pathInfo['filename']);
        $imageEntity->setExtension($pathInfo['extension'] ?? '');
        $this->saveImage($file, $imageEntity);
    }

    private function saveImage(File $file, Image $imageEntity): void
    {
        $format = $file->guessExtension();
        try {
            $image = $this->imageManipulator->saveImage($file->getPathname(), $this->getImageFilePath($imageEntity));
            $imageEntity->addFile($this->createImageFile($image)->setAsOriginal());
            $this->createSmallThumb($image, $imageEntity, $format);
            $this->createBigThumb($image, $imageEntity, $format);
        } catch (Exception) {
            $imageEntity->setError($this->translator->trans('Unable to resize image'));
            foreach ($imageEntity->getFiles() as $imageFile) {
                $imageEntity->removeFile($imageFile);
            }
            $this->removeImageFiles($imageEntity);
        }
    }

    private function createImageFile(ImageInterface $image): ImageFile
    {
        $size = $image->getSize();

        return (new ImageFile())
            ->setWidth($size->getWidth())
            ->setHeight($size->getHeight());
    }

    #[Pure]
    private function getImageFilePath(Image $imageEntity): string
    {
        return "{$this->basePath}/{$imageEntity->getCreatedAt()->format('Y-m-d')}/{$imageEntity->getId()}";
    }

    private function createSmallThumb(ImageInterface $image, Image $imageEntity, ?string $format): void
    {
        $thumbSmall = $this->imageManipulator->createThumb(
            $image,
            $this->getImageFilePath($imageEntity).self::THUMB_SIZE_SUFFIX_SMALL,
            self::THUMB_SIZE_SMALL,
            self::THUMB_SIZE_SMALL,
            false,
            $format
        );
        $imageEntity->addFile($this->createImageFile($thumbSmall)->setAsThumbSmall());
    }

    private function createBigThumb(ImageInterface $image, Image $imageEntity, ?string $format): void
    {
        $thumbSmall = $this->imageManipulator->createThumb(
            $image,
            $this->getImageFilePath($imageEntity).self::THUMB_SIZE_SUFFIX_BIG,
            self::THUMB_SIZE_BIG,
            self::THUMB_SIZE_BIG,
            true,
            $format
        );
        $imageEntity->addFile($this->createImageFile($thumbSmall)->setAsThumbBig());
    }
}
