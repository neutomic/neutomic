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

use Psl\Str;

use function preg_quote;

/**
 * A node representing a parameter.
 *
 * @psalm-type State = array{name: non-empty-string, regexp: null|non-empty-string}
 */
final readonly class ParameterNode implements Node
{
    /**
     * The name of the parameter.
     *
     * @var non-empty-string
     */
    private string $name;

    /**
     * The regular expression of the parameter.
     *
     * @var null|non-empty-string
     */
    private null|string $regexp;

    /**
     * Create a new {@see ParameterNode} instance.
     *
     * @param non-empty-string $name The name of the parameter.
     * @param null|non-empty-string $regexp The regular expression of the parameter.
     */
    public function __construct(string $name, null|string $regexp = null)
    {
        $this->name = $name;
        $this->regexp = $regexp;
    }

    /**
     * Get the name of the parameter.
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the regular expression of the parameter.
     *
     * @return null|non-empty-string
     */
    public function getRegexp(): null|string
    {
        return $this->regexp;
    }

    /**
     * @inheritDoc
     */
    public function toRegularExpression(string $delimiter): string
    {
        $re = $this->getRegexp();
        if ($re === null) {
            $re = '[^/]+';
        }

        return '(?<' . preg_quote($this->getName(), $delimiter) . '>' . $re . ')';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $re = $this->getRegexp();
        if ($re === null) {
            return '{' . $this->getName() . '}';
        }

        /** @var non-empty-string */
        return Str\format('{%s: #%s#}', $this->getName(), $re);
    }

    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'regexp' => $this->regexp,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
        /** @var State $data */
        $this->name = $data['name'];
        $this->regexp = $data['regexp'];
    }
}
