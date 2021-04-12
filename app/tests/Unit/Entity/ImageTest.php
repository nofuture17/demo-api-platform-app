<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImageTest extends KernelTestCase
{
    public function testGetFullName(): void
    {
        $image = (new Image())->setName('name');
        self::assertEquals('name', $image->getFullName());
        self::assertEquals('name.ext', $image->setExtension('ext')->getFullName());
    }
}
