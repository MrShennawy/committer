<?php

namespace MrShennawy\Committer;

use MrShennawy\Committer\git\Add;
use MrShennawy\Committer\git\Commit;
use MrShennawy\Committer\git\Status;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class GcCommand extends Command
{
    use RunCommands;

    const COMMIT_TYPES = [
        'FEATURE' => '<options=bold>FEATURE</>: A new feature for the user.',
        'FIX' => '<options=bold>FIX</>: A bug fix for the user.',
        'CHORE' => '<options=bold>CHORE</>: Routine tasks, maintenance, or refactors.',
        'DOCS' => '<options=bold>DOCS</>: Documentation changes.',
        'STYLE' => '<options=bold>STYLE</>: Code style changes (whitespace, formatting).',
        'REFACTOR' => '<options=bold>REFACTOR</>: Code changes that neither fix a bug nor add a feature.',
        'TEST' => '<options=bold>TEST</>: Adding or modifying tests.',
        'BUILD' => '<options=bold>BUILD</>: npm run build.'
    ];
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
        
        $selectFiles = $input->getOption('select') ? (new Add)->handle() : '.';

        // Handle the commit sentence
        $commitSentence = (new Commit)->handle();

        if ($input->getOption('build')) {
            $buildCmd = "npm run build";
            $question = new Question("Enter the build command", $buildCmd);
            $buildCmd = (new SymfonyStyle($input, $output))->askQuestion($question);

            $question = new ChoiceQuestion("Enter the number of build env", [
                'local', 'stg', 'prod'
            ]);
            $buildEnv = (new SymfonyStyle($input, $output))->askQuestion($question);
            $buildCmd = "$buildCmd".($buildEnv != 'local' ? ":$buildEnv" : '');
            $this->runCommands([$buildCmd], $input, $output);
            $type = 'BUILD';
            $commitSentence = $buildCmd;
            $issueId = null;
        }

        if (($process = $this->commitChanges(
            input: $input,
            output: $output,
            message: $commitSentence,
            files: $selectFiles
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
