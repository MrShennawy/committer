<?php

namespace MrShennawy\Committer\Support;

use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class Build
{
    use RunCommands;

    /**
     * Executes the build command.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return string The formatted command string.
     */
    public function command(InputInterface $input, OutputInterface $output)
    {
        $buildCmd = text(
            label: "Enter the build command",
            default: "npm run build",
            required: true
        );
        $this->runCommands([$buildCmd], $input, $output);
        $type = 'BUILD';
        return "$type: $buildCmd";
    }
}