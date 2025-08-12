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

namespace Neu\Component\Http\Message;

use Amp;
use Amp\Future;
use Closure;
use Psl\Async\Awaitable;
use Override;

/**
 * Provides an implementation for managing HTTP trailers asynchronously.
 *
 * @psalm-import-type Field from TrailerInterface
 * @psalm-import-type Value from TrailerInterface
 */
final readonly class Trailer implements TrailerInterface
{
    /**
     * The name of the trailer field.
     *
     * @var Field
     */
    private string $field;

    /**
     * The future or awaitable value of the trailer.
     *
     * @var Awaitable<Value>|Future<Value>
     */
    private Awaitable|Future $value;

    /**
     * Initializes a new {@see Trailer} instance with the specified field name and value provider.
     *
     * @param Field $field The name of the trailer field.
     * @param Awaitable<Value>|Future<Value> $value A future or awaitable that resolves to the value(s) of the trailer.
     */
    private function __construct(string $field, Awaitable|Future $value)
    {
        $this->field = $field;
        $this->value = $value;

        $value->ignore();
    }

    /**
     * Static factory method to create a new {@see Trailer} instance.
     *
     * Accepts a field name and a value provider that may be a future, an awaitable, or a closure returning the value.
     *
     * @param Field $field The name of the trailer field.
     * @param Awaitable<Value>|Future<Value>|(Closure(): Value) $value The value provider for the trailer.
     *
     * @return self Returns a new Trailer instance.
     */
    public static function create(string $field, Awaitable|Future|Closure $value): self
    {
        if ($value instanceof Closure) {
            $value = Amp\async($value);
        }

        return new self($field, $value);
    }

    /**
     * Convenience static factory method to create a Trailer instance directly from a given value.
     *
     * This method is useful when the value is already known and does not need to be computed asynchronously.
     *
     * @param Field $field The name of the trailer field.
     * @param Value $value The value of the trailer as an array.
     *
     * @return self Returns a new Trailer instance with the specified value.
     */
    public static function fromValue(string $field, array $value): self
    {
        return self::create($field, Awaitable::complete($value));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): array
    {
        return $this->value->await();
    }
}
