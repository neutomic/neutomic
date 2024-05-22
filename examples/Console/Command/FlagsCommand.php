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
use Neu\Component\Console\Table\TableFactoryTrait;

#[Command('flags', 'Example of using flags in command', flags: new Input\Bag\FlagBag([
    new Input\Definition\Flag('foo', alias: 'f', description: 'The foo flag', mode: Input\Definition\Mode::Required, stackable: true),
    new Input\Definition\Flag('bar', alias: 'b', description: 'The bar flag', mode: Input\Definition\Mode::Required, stackable: false),
    new Input\Definition\Flag('mux', alias: 'm', description: 'The mux flag', mode: Input\Definition\Mode::Optional, stackable: true),
    new Input\Definition\Flag('dux', alias: 'd', description: 'The dux flag', mode: Input\Definition\Mode::Optional, stackable: false),
]))]
final readonly class FlagsCommand implements CommandInterface
{
    use TableFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $foo = $input->getFlag('foo');
        $bar = $input->getFlag('bar');
        $mux = $input->getFlag('mux');
        $dux = $input->getFlag('dux');

        $headers = ['Flag', 'Alias', 'Negative Alias', 'Required', 'Stackable', 'Exists', 'Value'];

        $rows = [
            ['foo', $foo->getAlias(), $foo->getNegativeAlias(), $foo->getMode() === Input\Definition\Mode::Required ? 'Yes' : 'No', $foo->isStackable() ? 'Yes' : 'No', ($e = $foo->exists()) ? 'Yes' : 'No', $e ? (string) $foo->getValue() : 'N/A'],
            ['bar', $bar->getAlias(), $bar->getNegativeAlias(), $bar->getMode() === Input\Definition\Mode::Required ? 'Yes' : 'No', $bar->isStackable() ? 'Yes' : 'No', ($e = $bar->exists()) ? 'Yes' : 'No', $e ? (string) $bar->getValue() : 'N/A'],
            ['mux', $mux->getAlias(), $mux->getNegativeAlias(), $mux->getMode() === Input\Definition\Mode::Required ? 'Yes' : 'No', $mux->isStackable() ? 'Yes' : 'No', ($e = $mux->exists()) ? 'Yes' : 'No', $e ? (string) $mux->getValue() : 'N/A'],
            ['dux', $dux->getAlias(), $dux->getNegativeAlias(), $dux->getMode() === Input\Definition\Mode::Required ? 'Yes' : 'No', $dux->isStackable() ? 'Yes' : 'No', ($e = $dux->exists()) ? 'Yes' : 'No', $e ? (string) $dux->getValue() : 'N/A'],
        ];

        $this->createAsciiTable($output, $headers, $rows)->display();

        return 0;
    }
}
