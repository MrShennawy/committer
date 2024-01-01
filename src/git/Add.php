<?php

namespace MrShennawy\Committer\git;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Add
{
    use RunCommands;

    public function gitStatus(): Process
    {
        $process = new Process(['git', 'status', '-s']);

        $process->run();

        $output = trim($process->getOutput());
        var_dump(explode("\n", $output));

        // var_dump(explode("\n", $status));
        die;
    }
}