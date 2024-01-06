<?php

namespace MrShennawy\Committer;

use MrShennawy\Committer\Git\{Add, Commit, Status};
use MrShennawy\Committer\Support\Build;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\text;

class GcCommand extends Command
{
    use RunCommands;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('commit')
            ->setDescription('Alias for the git commit command')
            ->addOption('select', 's', InputOption::VALUE_NONE, 'Select the files')
            ->addOption('build', 'b', InputOption::VALUE_NONE, 'Build project before commit');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->greetings($input, $output);

        if ($input->getOption('build')) {
            $commitSentence = (new Build)->command($input, $output);
        } else {
            $selectFiles = $input->getOption('select') ? (new Add)->handle() : '.';
            // Handle the commit sentence
            $commitSentence = (new Commit)->handle();
        }

        if (($process = $this->commitChanges(
            input: $input,
            output: $output,
            message: $commitSentence,
            files: $selectFiles ?? '.'
        ))->isSuccessful()) {
            $output->write(PHP_EOL);
            $output->writeln(' <bg=blue;fg=white> INFO </> your code published successful!' . PHP_EOL);
        }

        return $process->getExitCode();
    }


    private function greetings(InputInterface $input, OutputInterface $output)
    {
        $output->write("<fg=yellow>
         ██████╗ ██████╗ ███╗   ███╗███╗   ███╗██╗████████╗████████╗███████╗██████╗ 
        ██╔════╝██╔═══██╗████╗ ████║████╗ ████║██║╚══██╔══╝╚══██╔══╝██╔════╝██╔══██╗
        ██║     ██║   ██║██╔████╔██║██╔████╔██║██║   ██║      ██║   █████╗  ██████╔╝
        ██║     ██║   ██║██║╚██╔╝██║██║╚██╔╝██║██║   ██║      ██║   ██╔══╝  ██╔══██╗
        ╚██████╗╚██████╔╝██║ ╚═╝ ██║██║ ╚═╝ ██║██║   ██║      ██║   ███████╗██║  ██║
         ╚═════╝ ╚═════╝ ╚═╝     ╚═╝╚═╝     ╚═╝╚═╝   ╚═╝      ╚═╝   ╚══════╝╚═╝  ╚═╝
            </>");

        if(!$this->isGitRepositoryInParent(getcwd())) {
            $output->write(PHP_EOL);
            (new SymfonyStyle($input, $output))
                ->block(': not a git repository (or any of the parent directories): .git', 'FATAL', 'fg=black;bg=yellow', ' ', true);
            die;
        }

        // Check files status
        (new Status)->command();
    }


    private function isGitRepositoryInParent($directory): bool
    {
        $currentDirectory = realpath($directory);
        while ($currentDirectory !== '/' && $currentDirectory !== false) {
            if (file_exists("$currentDirectory/.git"))
                return true; // Git repository found

            $currentDirectory = dirname($currentDirectory);
        }
        return false; // Git repository not found in any parent directory
    }

    /**
     * Commit any changes in the current working directory.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     * @param string $files
     * @return Process
     */
    protected function commitChanges(InputInterface $input, OutputInterface $output, string $message, string $files = '.'): Process
    {
        $commands = [
            "git add $files",
            "git commit -q -m \"$message\"",
            'git pull',
            'git push',
        ];

        return $this->runCommands($commands, $input, $output);
    }
}
