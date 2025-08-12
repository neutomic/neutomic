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

namespace Neu\Framework\DependencyInjection\Factory\Command\Advisory;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Command\Advisory\AdviceCommand;
use Override;

/**
 * Factory for creating a {@see AdviceCommand} instance.
 *
 * @implements FactoryInterface<AdviceCommand>
 */
final readonly class AdviceCommandFactory implements FactoryInterface
{
    /**
     * The advisory service to use.
     *
     * @var non-empty-string
     */
    private string $advisory;

    /**
     * @param null|non-empty-string $advisory The advisory service to use.
     */
    public function __construct(null|string $advisory = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): AdviceCommand
    {
        return new AdviceCommand(
            $container->getTyped($this->advisory, AdvisoryInterface::class),
        );
    }
}
