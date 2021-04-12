<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Service\ImageFileStorageInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DownloadController
{
    public function __construct(
        private ImageFileStorageInterface $fileStorage,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(Image $data, Request $request): BinaryFileResponse
    {
        if (null !== $data->getError()) {
            throw new BadRequestException($this->translator->trans('Image has uploading error'));
        }

        return (new BinaryFileResponse($this->fileStorage->getFilePath($data, $request->get(Image::THUMB_REQUEST_PROPERTY))))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $data->getFullName());
    }
}
