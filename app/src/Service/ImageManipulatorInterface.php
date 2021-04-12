<?php

declare(strict_types=1);

namespace App\Service;

use Imagine\Image\ImageInterface;

interface ImageManipulatorInterface
{
    public function saveImage(string $tmpFilePath, string $savePath): ImageInterface;

    public function createThumb(
        ImageInterface $image,
        string $path,
        int $width,
        int $height,
        bool $ratio,
        ?string $format
    ): ImageInterface;
}
