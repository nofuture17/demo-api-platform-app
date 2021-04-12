<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Service\ImageFileStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class DeleteController
{
    public function __construct(
        private ImageFileStorageInterface $fileStorage,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Image $data): Response
    {
        $this->fileStorage->removeImageFiles($data);
        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
