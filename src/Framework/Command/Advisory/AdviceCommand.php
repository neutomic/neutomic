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

namespace Neu\Framework\Command\Advisory;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Framework\Internal\Advisory\ConsoleTrait;

#[Command('advisory:advice', 'Retrieve advisory messages')]
final readonly class AdviceCommand implements CommandInterface
{
    use ConsoleTrait;

    private AdvisoryInterface $advisory;

    public function __construct(AdvisoryInterface $advisory)
    {
        $this->advisory = $advisory;
    }

    public function run(InputInterface $input, OutputInterface $output): ExitCode
    {
        $advices = $this->advisory->getAdvices();

        foreach ($advices as $advice) {
            $this->display($output, $advice);
        }

        return ExitCode::Success;
    }
}
