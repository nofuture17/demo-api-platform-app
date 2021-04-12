<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageFileStorageInterface
{
    public function saveFile(Image $imageEntity, UploadedFile $file): void;

    public function getFilePath(Image $image, ?string $thumb): string;

    public function removeImageFiles(Image $image): void;
}
