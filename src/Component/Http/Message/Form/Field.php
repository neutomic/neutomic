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

namespace Neu\Component\Http\Message\Form;

use Neu\Component\Http\Message\BodyInterface;
use Neu\Component\Http\Message\Internal\HeaderStorage;
use Neu\Component\Http\Message\Internal\MessageConvenienceMethodsTrait;

final readonly class Field implements FieldInterface
{
    use MessageConvenienceMethodsTrait;

    /**
     * The name of the field.
     *
     * @var non-empty-string
     */
    private string $name;

    /**
     * The body of the field.
     */
    private null|BodyInterface $body;

    /**
     * Creates a new {@see Field} instance.
     *
     * @param non-empty-string $name The name of the field.
     * @param HeaderStorage $headers The headers of the field.
     * @param null|BodyInterface $body The body of the field.
     *
     * @internal
     */
    private function __construct(string $name, HeaderStorage $headers, null|BodyInterface $body)
    {
        $this->name = $name;
        $this->headerStorage = $headers;
        $this->body = $body;
    }

    /**
     * Creates a new {@see Field} instance.
     *
     * @param non-empty-string $name The name of the field.
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers The headers of the field.
     * @param BodyInterface $body The body of the field.
     */
    public static function create(string $name, array $headers, BodyInterface $body): static
    {
        return new self($name, HeaderStorage::fromHeaders($headers), $body);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getBody(): null|BodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withBody(null|BodyInterface $body): static
    {
        return new self($this->name, $this->headerStorage, $body);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self($this->name, $headerStorage, $this->body);
    }
}
