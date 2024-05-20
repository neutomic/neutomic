<?php

declare(strict_types=1);

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
     */
    private string $filename;

    /**
     * The MIME type of the file.
     */
    private string $mimeType;

    /**
     * The extension of the file.
     */
    private ?string $extension;

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
     * @param HeaderStorage $headers The headers of the file.
     * @param null|BodyInterface $body The body of the file.
     *
     * @internal
     */
    private function __construct(string $name, string $filename, string $mimeType, ?string $extension, HeaderStorage $headers, ?BodyInterface $body)
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
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers The headers of the file.
     * @param BodyInterface $body The body of the file.
     */
    public static function create(string $name, string $filename, string $mimeType, ?string $extension, array $headers = [], ?BodyInterface $body = null): static
    {
        return new self($name, $filename, $mimeType, $extension, HeaderStorage::fromHeaders($headers), $body);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @inheritDoc
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(?BodyInterface $body): static
    {
        return new self($this->name, $this->filename, $this->mimeType, $this->extension, $this->headerStorage, $body);
    }

    /**
     * @inheritDoc
     */
    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self($this->name, $this->filename, $this->mimeType, $this->extension, $headerStorage, $this->body);
    }
}
