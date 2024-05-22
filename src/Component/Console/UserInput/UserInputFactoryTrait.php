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

namespace Neu\Component\Console\UserInput;

use Neu\Component\Console\Exception\InvalidArgumentException;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

trait UserInputFactoryTrait
{
    /**
     * Construct and return a new {@see ConfirmationUserInput} object given the default answer.
     *
     * @param null|non-empty-string $default
     *
     * @throws InvalidArgumentException If the default value is not in the list of choices.
     */
    protected function createConfirmationUserInput(InputInterface $input, OutputInterface $output, null|string $default = null): ConfirmationUserInput
    {
        $confirm = new ConfirmationUserInput($input, $output);
        $confirm->setDefault($default);

        return $confirm;
    }

    /**
     * Construct and return a new {@see MenuUserInput} object given the choices and display
     * message.
     *
     * @param array<non-empty-string, non-empty-string> $choices
     * @param null|non-empty-string $default
     *
     * @throws InvalidArgumentException If the default value is not in the list of choices.
     */
    protected function createMenuUserInput(InputInterface $input, OutputInterface $output, array $choices, null|string $default = null): MenuUserInput
    {
        $menu = new MenuUserInput($input, $output);
        $menu->setAcceptedValues($choices);
        $menu->setDefault($default);

        return $menu;
    }
}
