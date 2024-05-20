<?php

declare(strict_types=1);

namespace Neu\Component\Console\UserInput;

use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

trait UserInputFactoryTrait
{
    /**
     * Construct and return a new {@see ConfirmationUserInput} object given the default answer.
     *
     * @param null|non-empty-string $default
     */
    protected function createConfirmationUserInput(InputInterface $input, OutputInterface $output, ?string $default = null): ConfirmationUserInput
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
     */
    protected function createMenuUserInput(InputInterface $input, OutputInterface $output, array $choices, ?string $default = null): MenuUserInput
    {
        $menu = new MenuUserInput($input, $output);
        $menu->setAcceptedValues($choices);
        $menu->setDefault($default);

        return $menu;
    }
}
