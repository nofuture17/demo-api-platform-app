<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Image;
use App\Entity\ImageFile;
use App\Tests\Helper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImageFileTest extends KernelTestCase
{
    public function testGetUrl(): void
    {
        $file = (new ImageFile())->setImage(($image = new Image())->setId(Helper::generateImageId()));
        self::assertEquals("/images/download/{$image->getId()}", $file->getUrl());
        self::assertEquals("/images/download/{$image->getId()}?thumb=thumbSmall", $file->setAsThumbSmall()->getUrl());
        self::assertEquals("/images/download/{$image->getId()}?thumb=thumbBig", $file->setAsThumbBig()->getUrl());
    }
}
