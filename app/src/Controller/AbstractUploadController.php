<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ImageUploaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractUploadController
{
    protected const ERROR_UNEXPECTED_REQUEST_FORMAT = 'Unexpected request format';

    public function __construct(
        protected ImageUploaderInterface $imageUploader,
        protected TranslatorInterface $translator
    ) {
    }
}
