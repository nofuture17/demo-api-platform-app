<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\FileDataInput;
use App\Entity\Image;
use App\Service\ImageUploaderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UploadDataController extends AbstractUploadController
{
    public function __construct(
        ImageUploaderInterface $imageUploader,
        TranslatorInterface $translator,
        private SerializerInterface $serializer
    ) {
        parent::__construct($imageUploader, $translator);
    }

    /**
     * @return ArrayCollection<int, Image>
     */
    public function __invoke(Request $request): ArrayCollection
    {
        $files = $this->serializer->deserialize($request->getContent(), FileDataInput::class.'[]', $request->getPreferredFormat());
        if (!empty($files)) {
            $images = new ArrayCollection();
            foreach ($files as $file) {
                $images->add($this->imageUploader->handleJson($file));
            }

            return $images;
        }
        throw new BadRequestException($this->translator->trans(self::ERROR_UNEXPECTED_REQUEST_FORMAT));
    }
}
