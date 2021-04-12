<?php

declare(strict_types=1);

namespace App\Service;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Symfony\Component\Filesystem\Filesystem;

final class ImageManipulator implements ImageManipulatorInterface
{
    public function __construct(
        private Filesystem $filesystem,
        private ImagineInterface $imagine,
    ) {
    }

    public function saveImage(string $tmpFilePath, string $savePath): ImageInterface
    {
        $image = $this->imagine->open($tmpFilePath);
        $this->filesystem->copy($tmpFilePath, $savePath);
        $this->filesystem->remove($tmpFilePath);

        return $image;
    }

    public function createThumb(
        ImageInterface $image,
        string $path,
        int $width,
        int $height,
        bool $ratio,
        ?string $format
    ): ImageInterface {
        if ($ratio) {
            $thumb = $image->thumbnail(new Box($width, $height), ManipulatorInterface::THUMBNAIL_INSET);
        } else {
            $thumb = $image->copy()->resize(new Box($width, $height), );
        }
        $thumb->save($path, ['format' => $format]);

        return $thumb;
    }
}
