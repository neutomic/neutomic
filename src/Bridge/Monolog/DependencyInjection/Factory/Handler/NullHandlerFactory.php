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

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Handler;

use Monolog\Handler\NullHandler;
use Monolog\Level;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a null handler.
 *
 * @implements FactoryInterface<NullHandler>
 *
 * @psalm-suppress ArgumentTypeCoercion
 */
final readonly class NullHandlerFactory implements FactoryInterface
{
    /**
     * The logging level.
     */
    private int|string|Level $level;

    /**
     * Create a new {@see NullHandlerFactory} instance.
     *
     * @param null|int|string|Level $level The logging level.
     */
    public function __construct(null|int|string|Level $level = null)
    {
        $this->level = $level ?? Level::Debug;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new NullHandler($this->level);
    }
}
