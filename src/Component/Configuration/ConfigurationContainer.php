<?php

declare(strict_types=1);

namespace Neu\Component\Configuration;

use BackedEnum;
use Psl\Iter;
use Psl\Type;
use Psl\Type\TypeInterface;
use Psl\Vec;
use Throwable;
use Traversable;

use function array_replace_recursive;

final readonly class ConfigurationContainer implements ConfigurationContainerInterface
{
    /**
     * @param array<array-key, mixed> $entries
     */
    public function __construct(
        private array $entries,
        private bool $strict = false,
    ) {
    }

    /**
     * Create a new configuration container.
     *
     * @template K of array-key
     *
     * @param array<K, mixed> $entries
     *
     * @return static<K>
     */
    public static function create(array $entries = [], bool $strict = false): static
    {
        return new self($entries, $strict);
    }

    /**
     * @inheritDoc
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @inheritDoc
     */
    public function has(string|int $index): bool
    {
        return Iter\contains_key($this->entries, $index);
    }

    /**
     * @inheritDoc
     */
    public function get(string|int $index): mixed
    {
        if (!$this->has($index)) {
            throw $this->missingEntry($index);
        }

        return $this->entries[$index];
    }

    /**
     * @inheritDoc
     */
    public function getOfType(string|int $index, Type\TypeInterface $type): mixed
    {
        $value = $this->get($index);
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if ($this->strict) {
            try {
                return $type->assert($value);
            } catch (Type\Exception\AssertException $e) {
                throw $this->invalidEntry($index, 'is not of the expected type ' . $type->toString(), $e);
            }
        }

        try {
            return $type->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw $this->invalidEntry($index, 'cannot be coerced into the expected type ' . $type->toString(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getOfTypeOrDefault(int|string $index, TypeInterface $type, mixed $default): mixed
    {
        if (!$this->has($index)) {
            return $default;
        }

        return $this->getOfType($index, $type);
    }

    /**
     * @inheritDoc
     */
    public function isOfType(string|int $index, Type\TypeInterface $type): bool
    {
        if ($this->strict && (!$this->has($index) || !$type->matches($this->entries[$index]))) {
            return false;
        }

        try {
            $this->getOfType($index, $type);

            return true;
        } catch (Exception\ExceptionInterface) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getString(string|int $index): string
    {
        return $this->getOfType($index, Type\string());
    }

    /**
     * @inheritDoc
     */
    public function isString(int|string $index): bool
    {
        return $this->isOfType($index, Type\string());
    }

    /**
     * @inheritDoc
     */
    public function getInt(string|int $index): int
    {
        return $this->getOfType($index, Type\int());
    }

    /**
     * @inheritDoc
     */
    public function isInt(int|string $index): bool
    {
        return $this->isOfType($index, Type\int());
    }

    /**
     * @inheritDoc
     */
    public function getBool(string|int $index): bool
    {
        return $this->getOfType($index, Type\bool());
    }

    /**
     * @inheritDoc
     */
    public function isBool(int|string $index): bool
    {
        return $this->isOfType($index, Type\bool());
    }

    /**
     * @inheritDoc
     */
    public function getFloat(string|int $index): float
    {
        return $this->getOfType($index, Type\float());
    }

    /**
     * @inheritDoc
     */
    public function isFloat(int|string $index): bool
    {
        return $this->isOfType($index, Type\float());
    }

    /**
     * @inheritDoc
     */
    public function oneOf(int|string $index, array $values): mixed
    {
        return $this->getOfType($index, Type\union(...Vec\map($values, static fn(mixed $value) => Type\literal_scalar($value))));
    }

    /**
     * @inheritDoc
     */
    public function isOneOf(int|string $index, array $values): bool
    {
        return $this->isOfType($index, Type\union(...Vec\map($values, static fn(mixed $value) => Type\literal_scalar($value))));
    }

    /**
     * @inheritDoc
     */
    public function getEnum(int|string $index, string $enum): BackedEnum
    {
        return $this->getOfType($index, Type\backed_enum($enum));
    }


    /**
     * @inheritDoc
     */
    public function isEnum(int|string $index, string $enum): bool
    {
        return $this->isOfType($index, Type\backed_enum($enum));
    }

    /**
     * @inheritDoc
     */
    public function getContainer(int|string $index, ?bool $strict = null): ConfigurationContainerInterface
    {
        if (!$this->has($index)) {
            return new self([]);
        }

        $result = $this->getOfType($index, Type\dict(Type\array_key(), Type\mixed()));

        return new self($result, $strict ?? $this->strict);
    }

    /**
     * @inheritDoc
     */
    public function isContainer(int|string $index): bool
    {
        return $this->isOfType($index, Type\dict(Type\array_key(), Type\mixed()));
    }

    /**
     * @inheritDoc
     */
    public function merge(ConfigurationContainerInterface $container): static
    {
        return new self(
            array_replace_recursive($this->entries, $container->getAll()),
            $this->strict || $container->isStrict()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @inheritDoc
     */
    public function getIndices(): array
    {
        return Vec\keys($this->entries);
    }

    public function count(): int
    {
        return Iter\count($this->entries);
    }

    /**
     * @return Traversable<array-key, mixed>
     */
    public function getIterator(): Traversable
    {
        return Iter\Iterator::create($this->entries);
    }

    private function missingEntry(int|string $index): Exception\MissingEntryException
    {
        if (Type\string()->matches($index)) {
            $index = '"' . $index . '"';
        } else {
            $index = (string) $index;
        }

        return new Exception\MissingEntryException('Entry ' . $index . ' does not exist within the container.');
    }

    private function invalidEntry(int|string $index, string $message, ?Throwable $previous = null): Exception\InvalidEntryException
    {
        if (Type\string()->matches($index)) {
            $index = '"' . $index . '"';
        } else {
            $index = (string) $index;
        }

        return new Exception\InvalidEntryException('Entry ' . $index . ' value ' . $message, previous: $previous);
    }
}
