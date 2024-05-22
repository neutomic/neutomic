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

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

interface ProcessorInterface
{
    /**
     * Process a service after it has been created.
     *
     * @template T of object
     *
     * @param ContainerInterface $container The container that created the service.
     * @param DefinitionInterface $definition The definition of the service.
     * @param T $service The service to process.
     *
     * @throws ExceptionInterface
     *
     * @return T
     *
     * @note The definition object cannot be modified, any changes made to the definition will be discarded.
     */
    public function process(ContainerInterface $container, DefinitionInterface $definition, object $service): object;
}
