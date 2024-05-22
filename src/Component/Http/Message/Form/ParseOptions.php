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

/**
 * A configuration class defining options for parsing HTTP form data.
 *
 * This class allows for dynamic configuration of limits on the number of fields, file uploads, and the size of the body in form submissions.
 */
final readonly class ParseOptions
{
    /**
     * Default maximum number of fields allowed in a form to prevent excessive resource usage.
     */
    private const int DEFAULT_FIELD_COUNT_LIMIT = 100;

    /**
     * Default maximum number of files allowed in a form to prevent excessive resource usage.
     */
    private const int DEFAULT_FILE_COUNT_LIMIT = 10;

    /**
     * The maximum number of fields that a form can contain. This limit helps in managing resource allocation
     * and preventing potential abuse through excessively large forms.
     */
    public int $fieldCountLimit;

    /**
     * The maximum size of the form body that can be processed, in bytes.
     *
     * If set, this value is used to explicitly limit the size of incoming form data.
     *
     * A `null` value indicates that the default server limit should be used, which can be overridden for specific scenarios.
     */
    public null|int $bodySizeLimit;

    /**
     * The allowed file extensions for uploaded files.
     *
     * A `null` value indicates that all file extensions are allowed.
     */
    public null|array $allowedFileExtensions;

    /**
     * The maximum number of files that a form can contain. This limit helps in managing resource allocation
     * and preventing potential abuse through excessive file uploads.
     */
    public int $fileCountLimit;

    /**
     * Indicates whether the parser should allow files without extensions.
     */
    public bool $allowFilesWithoutExtensions;

    /**
     * Constructs a new ParseOptions instance with optional customization for field count, body size, file extensions, file count limits, and allowance for files without extensions.
     *
     * @param int $fieldCountLimit The maximum number of fields allowed in a form. Defaults to the class constant.
     * @param ?int $bodySizeLimit The explicit maximum body size allowed in bytes, or null to use the server's default limit.
     * @param ?array $allowedFileExtensions The allowed file extensions, or null to allow all extensions.
     * @param int $fileCountLimit The maximum number of files allowed in a form. Defaults to the class constant.
     * @param bool $allowFilesWithoutExtensions Indicates whether files without extensions are allowed. Defaults to true.
     */
    public function __construct(
        int $fieldCountLimit = self::DEFAULT_FIELD_COUNT_LIMIT,
        null|int $bodySizeLimit = null,
        null|array $allowedFileExtensions = null,
        int $fileCountLimit = self::DEFAULT_FILE_COUNT_LIMIT,
        bool $allowFilesWithoutExtensions = true
    ) {
        $this->fieldCountLimit = $fieldCountLimit;
        $this->bodySizeLimit = $bodySizeLimit;
        $this->allowedFileExtensions = $allowedFileExtensions;
        $this->fileCountLimit = $fileCountLimit;
        $this->allowFilesWithoutExtensions = $allowFilesWithoutExtensions;
    }

    /**
     * Creates an instance of ParseOptions with a specified maximum field count, retaining the default or current maximum body size and file settings.
     *
     * @param int $fieldCountLimit The desired maximum number of form fields.
     *
     * @return self Returns a new instance of ParseOptions with the specified field count limit.
     */
    public static function fromFieldCountLimit(int $fieldCountLimit): self
    {
        return new self($fieldCountLimit);
    }

    /**
     * Creates an instance of ParseOptions with a specified maximum body size, retaining the default maximum field count and file settings.
     *
     * @param ?int $bodySizeLimit The desired maximum body size in bytes, or null to use the server's default limit.
     *
     * @return self Returns a new instance of ParseOptions with the specified body size limit.
     */
    public static function fromBodySizeLimit(null|int $bodySizeLimit): self
    {
        return new self(self::DEFAULT_FIELD_COUNT_LIMIT, $bodySizeLimit);
    }

    /**
     * Returns a new instance of ParseOptions with an updated field count limit, preserving the current maximum body size and file settings.
     *
     * @param int $fieldCountLimit The new maximum number of form fields.
     *
     * @return self Returns a new instance of ParseOptions with the updated field count limit.
     */
    public function withFieldCountLimit(int $fieldCountLimit): self
    {
        return new self($fieldCountLimit, $this->bodySizeLimit, $this->allowedFileExtensions, $this->fileCountLimit, $this->allowFilesWithoutExtensions);
    }

    /**
     * Returns a new instance of ParseOptions with an updated body size limit, preserving the current field count and file settings.
     *
     * @param ?int $bodySizeLimit The new maximum body size in bytes, or null to use the server's default limit.
     *
     * @return self Returns a new instance of ParseOptions with the updated body size limit.
     */
    public function withBodySizeLimit(null|int $bodySizeLimit): self
    {
        return new self($this->fieldCountLimit, $bodySizeLimit, $this->allowedFileExtensions, $this->fileCountLimit, $this->allowFilesWithoutExtensions);
    }

    /**
     * Returns a new instance of ParseOptions with updated allowed file extensions, preserving the current field count, body size, and file count settings.
     *
     * @param ?array $allowedFileExtensions The new allowed file extensions, or null to allow all extensions.
     *
     * @return self Returns a new instance of ParseOptions with the updated allowed file extensions.
     */
    public function withAllowedFileExtensions(null|array $allowedFileExtensions): self
    {
        return new self($this->fieldCountLimit, $this->bodySizeLimit, $allowedFileExtensions, $this->fileCountLimit, $this->allowFilesWithoutExtensions);
    }

    /**
     * Returns a new instance of ParseOptions with an updated file count limit, preserving the current field count, body size, and file extension settings.
     *
     * @param int $fileCountLimit The new maximum number of files allowed in a form.
     *
     * @return self Returns a new instance of ParseOptions with the updated file count limit.
     */
    public function withFileCountLimit(int $fileCountLimit): self
    {
        return new self($this->fieldCountLimit, $this->bodySizeLimit, $this->allowedFileExtensions, $fileCountLimit, $this->allowFilesWithoutExtensions);
    }

    /**
     * Returns a new instance of ParseOptions with an updated setting for allowing files without extensions, preserving the current field count, body size, and file settings.
     *
     * @param bool $allowFilesWithoutExtensions Indicates whether files without extensions are allowed.
     *
     * @return self Returns a new instance of ParseOptions with the updated setting for allowing files without extensions.
     */
    public function withAllowFilesWithoutExtensions(bool $allowFilesWithoutExtensions): self
    {
        return new self($this->fieldCountLimit, $this->bodySizeLimit, $this->allowedFileExtensions, $this->fileCountLimit, $allowFilesWithoutExtensions);
    }
}
