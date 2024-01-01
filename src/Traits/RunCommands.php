<?php

namespace MrShennawy\Committer\Traits;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait RunCommands
{
    /**
     * Run the given commands.
     *
     * @param array|string $commands
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $env
     * @return Process
     */
    protected function runCommands($commands, InputInterface $input, OutputInterface $output, array $env = []): Process
    {
        $commands = is_array($commands) ? $commands : [$commands];
        if (!$output->isDecorated()) {
            $commands = array_map(function ($value) {
                if (substr($value, 0, 5) === 'chmod') return $value;

                if (substr($value, 0, 3) === 'git') return $value;

                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                if (substr($value, 0, 5) === 'chmod') {
                    return $value;
                }

                if (substr($value, 0, 3) === 'git') {
                    return $value;
                }

                return $value . ' --quiet';
            }, $commands);
        }

        $process = Process::fromShellCommandline(implode(' && ', $commands), null, $env, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }
}