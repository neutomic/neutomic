<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Internal;

/**
 * @require-implements MessageInterface
 */
trait MessageConvenienceMethodsTrait
{
    /**
     * The headers of the message.
     */
    protected readonly HeaderStorage $headerStorage;

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headerStorage->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return $this->headerStorage->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): ?array
    {
        return $this->headerStorage->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): ?string
    {
        return $this->headerStorage->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, array|string $value): static
    {
        return $this->cloneWithHeaderStorage(
            $this->headerStorage->withHeader($name, $value),
        );
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, array|string $value): static
    {
        return $this->cloneWithHeaderStorage(
            $this->headerStorage->withAddedHeader($name, $value),
        );
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): static
    {
        return $this->cloneWithHeaderStorage(
            $this->headerStorage->withoutHeader($name),
        );
    }

    /**
     * Clone the message with the given header storage.
     */
    abstract protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static;
}
