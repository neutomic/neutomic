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

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Neu\Component\Console\UserInput\UserInputFactoryTrait;

#[Command('user-input', description: 'Example of using user input')]
final readonly class UserInputCommand implements CommandInterface
{
    use UserInputFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $confirmation = $this->createConfirmationUserInput($input, $output, 'n');
        $answer = $confirmation->prompt('Do you want to continue?');
        if ($answer) {
            $output->writeLine('You chose to continue.');
        } else {
            $output->writeLine('You chose to stop, but we will continue anyways.');
        }

        $menu = $this->createMenuUserInput($input, $output, [
            'car' => 'Car',
            'bike' => 'Bike / Motorcycle',
            'bus' => 'Bus / Coach',
            'train' => 'Train / Subway',
            'plane' => 'Plane / Helicopter',
        ]);

        $menu->setDefault('bike');

        $choice = $menu->prompt('What is your favorite mode of transportation?');

        $output->writeLine('You picked: ' . $choice);


        return 0;
    }
}
