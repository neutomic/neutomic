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

namespace Neu\Component\Http\Router\PatternParser\Node;

use function preg_quote;
use function var_export;

/**
 * A node representing a literal.
 *
 * @psalm-type State = array{text: non-empty-string}
 */
final readonly class LiteralNode implements Node
{
    /**
     * The text of the literal.
     *
     * @var non-empty-string
     */
    private string $text;

    /**
     * Create a new {@see LiteralNode} instance.
     *
     * @param non-empty-string $text The text of the literal.
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * Get the text of the literal.
     *
     * @return non-empty-string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @internal
     */
    public function toRegularExpression(string $delimiter): string
    {
        /** @var non-empty-string */
        return preg_quote($this->getText(), $delimiter);
    }

    /**
     * @internal
     */
    public function toString(): string
    {
        /** @var non-empty-string */
        return var_export($this->getText(), true);
    }

    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [
            'text' => $this->text,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
        /** @var State $data */
        $this->text = $data['text'];
    }
}
