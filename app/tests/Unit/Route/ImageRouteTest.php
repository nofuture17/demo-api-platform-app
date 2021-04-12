<?php

declare(strict_types=1);

namespace App\Tests\Unit\Route;

use App\Controller\DeleteController;
use App\Controller\DownloadController;
use App\Controller\GetFailedController;
use App\Controller\GetSuccessController;
use App\Controller\UploadDataController;
use App\Controller\UploadFileController;
use App\Entity\Image;
use App\Tests\AbstractRouteTest;

final class ImageRouteTest extends AbstractRouteTest
{
    public function testGetList(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images', 'GET'),
            'api_platform.action.get_collection',
            Image::class
        );
    }

    public function testGetSuccessList(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/success', 'GET'),
            GetSuccessController::class,
            Image::class
        );
    }

    public function testGetFailedList(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/failed', 'GET'),
            GetFailedController::class,
            Image::class
        );
    }

    public function testUploadFile(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/upload/file', 'POST'),
            UploadFileController::class,
            Image::class
        );
    }

    public function testUploadData(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/upload/data', 'POST'),
            UploadDataController::class,
            Image::class
        );
    }

    public function testDelete(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/{id}', 'DELETE'),
            DeleteController::class,
            Image::class
        );
    }

    public function testDownload(): void
    {
        self::assertInfo(
            $this->getRouteInfo('/images/download/{id}', 'GET'),
            DownloadController::class,
            Image::class
        );
    }
}
