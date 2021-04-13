<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\UploadDataController;
use App\DTO\FileDataInput;
use App\Entity\Image;
use App\Service\ImageUploaderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UploadDataControllerTest extends KernelTestCase
{
    /**
     * @var ImageUploaderInterface|MockObject
     */
    protected ImageUploaderInterface | MockObject $imageUploader;
    /**
     * @var TranslatorInterface|MockObject
     */
    protected TranslatorInterface | MockObject $translator;
    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface | MockObject $serializer;

    public function testInvokeWithError(): void
    {
        $controller = $this->createController();
        $exception = null;
        try {
            $controller->__invoke(new Request());
        } catch (BadRequestException $exception) {
        }
        self::assertEquals('Unexpected request format', $exception->getMessage());
    }

    public function testInvoke(): void
    {
        $images = [];
        $files = [
            new FileDataInput(),
            new FileDataInput(),
        ];
        foreach ($files as $_) {
            $images[] = new Image();
        }
        $controller = $this->createController();
        $this->imageUploader->expects(self::exactly(count($files)))
            ->method('handleJson')
            ->withConsecutive(...array_map(static fn (FileDataInput $file) => [$file], $files))
            ->willReturnOnConsecutiveCalls(...$images);
        $request = $this->createMock(Request::class);
        $request->method('getContent')
            ->willReturn($json = json_encode($files));
        $request->method('getPreferredFormat')
            ->willReturn($format = 'json');
        $this->serializer->expects(self::once())
            ->method('deserialize')
            ->with($json, FileDataInput::class.'[]', $format)
            ->willReturn($files);
        self::assertEquals(new ArrayCollection($images), $controller->__invoke($request));
    }

    private function createController(): UploadDataController
    {
        $this->imageUploader = $this->createMock(ImageUploaderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->willReturnCallback(static function () {
                return current(func_get_args());
            });
        $this->serializer = $this->createMock(SerializerInterface::class);

        return new UploadDataController($this->imageUploader, $this->translator, $this->serializer);
    }
}
