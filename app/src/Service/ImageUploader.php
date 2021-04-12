<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\FileDataInput;
use App\Entity\Image;
use App\Exception\LogicException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImageUploader implements ImageUploaderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ImageFileStorageInterface $fileStorage,
        private TranslatorInterface $translator,
        private Filesystem $filesystem,
        private HttpClientInterface $httpClient
    ) {
    }

    public function handleFile(UploadedFile $file): Image
    {
        $image = new Image();
        $this->saveImage($image, $file);

        return $image;
    }

    public function handleJson(FileDataInput $file): Image
    {
        $image = new Image();
        if (empty($file->name)) {
            $image->setError($this->translator->trans('File name is required'));
        } elseif (empty($file->content) || !is_string($file->content)) {
            $image->setError($this->translator->trans('File must have url or base64 content'));
        } else {
            try {
                $this->saveImage($image, $this->getFileFromContent($file));
            } catch (LogicException $exception) {
                $image->setError($exception->getMessage());
            }
        }

        return $image;
    }

    private function saveImage(Image $image, UploadedFile $file): void
    {
        $this->entityManager->persist($image);
        $this->fileStorage->saveFile($image, $file);
        $this->entityManager->flush();
    }

    /**
     * @throws LogicException
     */
    private function getFileFromContent(FileDataInput $file): UploadedFile
    {
        if (filter_var($file->content, FILTER_VALIDATE_URL) || 1 === preg_match('/https?:\/\//', $file->content)) {
            $content = $this->getContentByUrl($file->content);
        } else {
            $content = base64_decode($file->content);
        }

        if (!empty($content)) {
            $path = $this->filesystem->tempnam('/tmp', '');
            $this->filesystem->dumpFile($path, $content);

            return new UploadedFile($path, $file->name);
        }

        throw new LogicException($this->translator->trans('Wrong file content'));
    }

    private function getContentByUrl(string $url): string
    {
        $content = null;
        try {
            $response = $this->httpClient->request(Request::METHOD_GET, $url);
            if (Response::HTTP_OK === $response->getStatusCode()) {
                $content = $response->getContent();
            }
        } catch (ExceptionInterface) {
        }

        if (null === $content) {
            throw new LogicException('Unable to get image by URL');
        }

        return $content;
    }
}
