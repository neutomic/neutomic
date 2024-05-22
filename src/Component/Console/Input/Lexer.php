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

namespace Neu\Component\Console\Input;

use Iterator;
use Psl\Dict;
use Psl\Iter;
use Psl\Regex;
use Psl\Str\Byte;
use Psl\Vec;

/**
 * The `Lexer` handles all parsing and pairing of the provided input.
 *
 * @psalm-type Token = array{raw: string, value: string}
 *
 * @implements Iterator<Token>
 *
 * @psalm-suppress RiskyTruthyFalsyComparison
 */
final class Lexer implements Iterator
{
    /**
     * The current position in the `items` of the lexer.
     */
    protected int $position = 0;

    /**
     * The current length of available values remaining in the lexer.
     */
    protected int $length = 0;

    /**
     * The current value the lexer is pointing to.
     *
     * @var Token
     */
    protected array $current;

    /**
     * Whether the lexer is on its first item or not.
     */
    protected bool $first = true;

    /**
     * Construct a new `InputLexer` given the provided structure of inputs.
     *
     * @param list<string> $items The items to traverse through
     */
    public function __construct(
        /**
         * Data structure of all items that have yet to be retrieved.
         */
        private array $items = [],
    ) {
        $this->length = Iter\count($items);
        $this->current = ['value' => '', 'raw' => ''];
    }

    /**
     * Return whether the given value is representing notation for an argument.
     */
    public static function isAnnotated(string $value): bool
    {
        return self::isLong($value) || self::isShort($value);
    }

    /**
     * Determine if the given value is representing a long argument (i.e., --foo).
     */
    public static function isLong(string $value): bool
    {
        return Byte\starts_with($value, '--');
    }

    /**
     * Determine if the given value is representing a short argument (i.e., -f).
     */
    public static function isShort(string $value): bool
    {
        return !self::isLong($value) && Byte\starts_with($value, '-');
    }

    /**
     * Retrieve the current item the lexer is pointing to.
     *
     * @return Token
     */
    public function current(): array
    {
        return $this->current;
    }

    /**
     * Return whether the lexer has reached the end of its parsable items or not.
     */
    public function end(): bool
    {
        return ($this->position + 1) === $this->length;
    }

    /**
     * Retrieve the current position of the lexer.
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Progress the lexer to its next item (if available).
     */
    public function next(): void
    {
        if ($this->valid()) {
            $this->shift();
        }
    }

    /**
     * Return whether the lexer has any more items to parse.
     */
    public function valid(): bool
    {
        return ($this->position < $this->length);
    }

    /**
     * Progress the lexer to its next available item. If the item contains a value
     * an argument is representing, separate them and add the value back to the
     * available items to parse.
     */
    public function shift(): void
    {
        /** @var string|null $key */
        $key = Iter\first($this->items);
        $this->items = Vec\values(Dict\drop($this->items, 1));

        if ($key !== null && $matches = Regex\first_match($key, "#\A([^\s'\"=]+)=(.+?)$#")) {
            $key = $matches[1];
            $this->items = Vec\concat([$matches[2]], $this->items);
        } else {
            $this->position++;
        }

        if ($key === null) {
            return;
        }

        $this->current = $this->processInput($key);

        $this->explode();
    }

    /**
     * Create and return RawInput given a raw string value.
     *
     * @return Token
     */
    public function processInput(string $input): array
    {
        $raw = $input;
        $value = $input;

        if (self::isLong($input)) {
            $value = Byte\slice($input, 2);
        } elseif (self::isShort($input)) {
            $value = Byte\slice($input, 1);
        }

        return ['raw' => $raw, 'value' => $value];
    }

    /**
     * If the current item is a string of short input values or the string contains
     * a value a flag is representing, separate them and add them to the available
     * items to parse.
     */
    private function explode(): void
    {
        if (!self::isShort($this->current['raw']) || Byte\length($this->current['value']) <= 1) {
            return;
        }

        $exploded = Byte\chunk($this->current['value']);
        $value = Iter\last($exploded);

        $this->current = [
            'value' => $value,
            'raw' => '-' . $value,
        ];

        foreach (Dict\take($exploded, Iter\count($exploded) - 1) as $piece) {
            $this->unshift('-' . $piece);
        }
    }

    /**
     * Add an item back to the items that have yet to be parsed.
     */
    public function unshift(string $item): void
    {
        $this->items = Vec\concat([$item], $this->items);
        $this->length++;
    }

    /**
     * Peek ahead to the next available item without progressing the lexer.
     *
     * @return Token|null
     */
    public function peek(): null|array
    {
        if (Iter\count($this->items) > 0) {
            return $this->processInput($this->items[0]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->shift();
        if ($this->first) {
            $this->position = 0;
            $this->first = false;
        }
    }
}
