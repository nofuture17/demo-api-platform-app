<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\FileDataInput;
use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageUploaderInterface
{
    public function handleFile(UploadedFile $file): Image;

    public function handleJson(FileDataInput $file): Image;
}
