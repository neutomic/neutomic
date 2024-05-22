<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Http\Message\Internal;

use Neu\Component\Http\Message\ProtocolVersion;
use Neu\Component\Http\Message\TrailerInterface;
use Psl\Iter;

/**
 * @require-implements ExchangeInterface
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 */
trait ExchangeConvenienceMethodsTrait
{
    use MessageConvenienceMethodsTrait;

    /**
     * The HTTP protocol version.
     */
    protected readonly ProtocolVersion $protocolVersion;

    /**
     * The exchange trailers.
     *
     * @var array<non-empty-string, TrailerInterface>
     */
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
        /** @var array<non-empty-string, TrailerInterface> */
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
    public function getTrailer(string $field): null|TrailerInterface
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
     * @param array<non-empty-string, TrailerInterface> $trailers
     *
     * @return static
     */
    abstract protected function cloneWithTrailers(array $trailers): static;
}
