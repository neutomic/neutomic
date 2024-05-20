<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Internal\PatternParser;

use function preg_quote;
use function var_export;

/**
 * @internal
 */
final readonly class LiteralNode implements Node
{
    /**
     * @var non-empty-string
     */
    private string $text;

    /**
     * @param non-empty-string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function toStringForDebug(): string
    {
        return var_export($this->getText(), true);
    }

    public function asRegexp(string $delimiter): string
    {
        return preg_quote($this->getText(), $delimiter);
    }

    /**
     * @return array{text: string}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['text' => $this->text];
    }

    /**
     * @param array{text: string} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        ['text' => $this->text] = $data;
    }
}
