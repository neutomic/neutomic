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

namespace Neu\Component\Http\Router\Internal\PatternParser;

use Psl\Str;

use function preg_quote;

/**
 * @internal
 */
final readonly class ParameterNode implements Node
{
    public function __construct(
        private string $name,
        private null|string $regexp,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegexp(): null|string
    {
        return $this->regexp;
    }

    public function toStringForDebug(): string
    {
        $re = $this->getRegexp();
        if ($re === null) {
            return '{' . $this->getName() . '}';
        }

        return Str\format('{%s: #%s#}', $this->getName(), $re);
    }

    public function asRegexp(string $delimiter): string
    {
        $re = $this->getRegexp();
        if ($re === null) {
            $re = '[^/]+';
        }

        return '(?<' . preg_quote($this->getName(), $delimiter) . '>' . $re . ')';
    }

    /**
     * @return array{name: string, regexp: string|null}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['name' => $this->name, 'regexp' => $this->regexp];
    }

    /**
     * @param array{name: string, regexp: string|null} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        ['name' => $this->name, 'regexp' => $this->regexp] = $data;
    }
}
