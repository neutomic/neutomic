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

final readonly class File implements FileInterface
{
    use MessageConvenienceMethodsTrait;

    /**
     * The name of the field.
     *
     * @var non-empty-string
     */
    private string $name;

    /**
     * The filename of the file.
     *
     * @var non-empty-string
     */
    private string $filename;

    /**
     * The MIME type of the file.
     *
     * @var non-empty-string
     */
    private string $mimeType;

    /**
     * The extension of the file.
     *
     * @var null|non-empty-string
     */
    private null|string $extension;

    /**
     * The body of the field.
     */
    private null|BodyInterface $body;

    /**
     * Creates a new {@see File} instance.
     *
     * @param non-empty-string $name The name of the file.
     * @param non-empty-string $filename The filename of the file.
     * @param non-empty-string $mimeType The MIME type of the file.
     * @param null|non-empty-string $extension The extension of the file.
     * @param HeaderStorage $headers The headers of the file.
     * @param null|BodyInterface $body The body of the file.
     *
     * @internal
     */
    private function __construct(string $name, string $filename, string $mimeType, null|string $extension, HeaderStorage $headers, null|BodyInterface $body)
    {
        $this->name = $name;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->extension = $extension;
        $this->headerStorage = $headers;
        $this->body = $body;
    }

    /**
     * Creates a new {@see File} instance.
     *
     * @param non-empty-string $name The name of the file.
     * @param non-empty-string $filename The filename of the file.
     * @param non-empty-string $mimeType The MIME type of the file.
     * @param null|non-empty-string $extension The extension of the file.
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers The headers of the file.
     * @param BodyInterface $body The body of the file.
     */
    public static function create(string $name, string $filename, string $mimeType, null|string $extension, array $headers = [], null|BodyInterface $body = null): static
    {
        return new self($name, $filename, $mimeType, $extension, HeaderStorage::fromHeaders($headers), $body);
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
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getExtension(): null|string
    {
        return $this->extension;
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
        return new self($this->name, $this->filename, $this->mimeType, $this->extension, $this->headerStorage, $body);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self($this->name, $this->filename, $this->mimeType, $this->extension, $headerStorage, $this->body);
    }
}
