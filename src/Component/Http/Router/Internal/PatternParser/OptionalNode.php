<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Internal\PatternParser;

/**
 * @internal
 */
final readonly class OptionalNode implements Node
{
    public function __construct(private PatternNode $pattern)
    {
    }

    public function getPattern(): PatternNode
    {
        return $this->pattern;
    }

    public function toStringForDebug(): string
    {
        return '?' . $this->pattern->toStringForDebug();
    }

    public function asRegexp(string $delimiter): string
    {
        return '(?:' . $this->pattern->asRegexp($delimiter) . ')?';
    }

    /**
     * @param array{pattern: PatternNode} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        $this->pattern = $data['pattern'];
    }

    /**
     * @return array{pattern: PatternNode}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['pattern' => $this->pattern];
    }
}
