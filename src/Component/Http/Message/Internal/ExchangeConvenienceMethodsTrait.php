<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Internal;

use Neu\Component\Http\Message\ProtocolVersion;
use Neu\Component\Http\Message\TrailerInterface;
use Psl\Iter;

/**
 * @require-implements ExchangeInterface
 */
trait ExchangeConvenienceMethodsTrait
{
    use MessageConvenienceMethodsTrait;

    /**
     * The HTTP protocol version.
     */
    protected readonly ProtocolVersion $protocolVersion;

    protected readonly array $trailers;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): ProtocolVersion
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function getTrailers(): array
    {
        return $this->trailers;
    }

    /**
     * @inheritDoc
     */
    public function hasTrailer(string $field): bool
    {
        return Iter\contains_key($this->trailers, $field);
    }

    /**
     * @inheritDoc
     */
    public function getTrailer(string $field): ?TrailerInterface
    {
        return $this->trailers[$field] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function withTrailer(TrailerInterface $trailer): static
    {
        $trailers = $this->trailers;
        $trailers[$trailer->getField()] = $trailer;

        return $this->cloneWithTrailers($trailers);
    }

    /**
     * @inheritDoc
     */
    public function withoutTrailer(string $field): static
    {
        $trailers = $this->trailers;
        unset($trailers[$field]);

        return $this->cloneWithTrailers($trailers);
    }

    /**
     * @param array<string, TrailerInterface> $trailers
     *
     * @return static
     */
    abstract protected function cloneWithTrailers(array $trailers): static;
}
