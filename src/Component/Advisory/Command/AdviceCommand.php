<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Command;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Advisory\Internal\ConsoleTrait;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

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
