<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\Core\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use App\DTO\FileDataInput;
use ArrayObject;

/**
 * @codeCoverageIgnore
 */
final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private SchemaFactoryInterface $schemaFactory
    ) {
    }

    /**
     * @param array<int, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        if (null !== $schemas = $openApi->getComponents()->getSchemas()) {
            $this->appendSchema($schemas, $this->schemaFactory->buildSchema(FileDataInput::class));
        }

        return $openApi;
    }

    /**
     * @param ArrayObject<string, mixed> $schemas
     * @param ArrayObject<string, mixed> $new
     */
    private function appendSchema(ArrayObject $schemas, ArrayObject $new): void
    {
        /* @phpstan-ignore-next-line */
        foreach ($new->getDefinitions() as $key => $definition) {
            $schemas[$key] = $definition;
        }
    }
}
