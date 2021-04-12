<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

final class UploadFileController extends AbstractUploadController
{
    /**
     * @return ArrayCollection<int, Image>
     */
    public function __invoke(Request $request): ArrayCollection
    {
        if (is_array($files = $request->files->get(Image::FILES_REQUEST_PROPERTY))) {
            $images = new ArrayCollection();
            foreach ($files as $file) {
                $images->add($this->imageUploader->handleFile($file));
            }

            return $images;
        }
        throw new BadRequestException($this->translator->trans(self::ERROR_UNEXPECTED_REQUEST_FORMAT));
    }
}
