<?php

namespace MrShennawy\Committer;

use MrShennawy\Committer\git\Add;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class CCommand extends Command
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
        $this->setName('c')
            ->setDescription('Alias for the git commit command')
            ->addOption('build', 'b', InputOption::VALUE_NONE, 'build');
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
            $output->writeln('<bg=blue;fg=white> FATAL </><fg=red>: not a git repository (or any of the parent directories): .git </>' . PHP_EOL);
            die;
        }

        // (new Add())->gitStatus();

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
        } else {
            $type = array_search($this->commitType($input, $output), self::COMMIT_TYPES);
            $output->writeln("<bg=yellow;fg=black> => $type </>");

            $commitSentence = $this->commitSentence($input, $output);
            $output->writeln("<bg=yellow;fg=black> => $type: $commitSentence </>");

            $issueId = $this->issueId($input, $output);
            $output->writeln("<bg=yellow;fg=black> The commit =></><fg=green> {$type}</>: <fg=white>{$commitSentence}</>" . ($issueId ? " - <fg=yellow>$issueId </>" : "") . PHP_EOL);
        }


        $message = "{$type}: $commitSentence" . ($issueId ? " - $issueId" : "");
        if (($process = $this->commitChanges($message, $input, $output))->isSuccessful()) {
            $output->write(PHP_EOL);
            $output->writeln(' <bg=blue;fg=white> INFO </> your code published successful!' . PHP_EOL);
        }

        return $process->getExitCode();
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

    protected function commitType(InputInterface $input, OutputInterface $output)
    {
        $stacks = array_values(self::COMMIT_TYPES);
        $question = new ChoiceQuestion("Enter the number of the desired commit type", $stacks);
        return (new SymfonyStyle($input, $output))->askQuestion($question);
    }

    protected function commitSentence(InputInterface $input, OutputInterface $output)
    {
        $question = new Question("Enter the commit sentence");
        $commit = (new SymfonyStyle($input, $output))->askQuestion($question);
        if(!$commit) {
            $output->writeln(' <bg=red;fg=white> ERROR </><fg=red>: The commit sentence is required </>' . PHP_EOL);
            $this->commitSentence($input, $output);
        }
        return $commit;
    }

    protected function issueId(InputInterface $input, OutputInterface $output)
    {
        $question = new Question("Enter an Issue ID <fg=yellow>(Optional)</>", null);
        return (new SymfonyStyle($input, $output))->askQuestion($question);
    }

    /**
     * Commit any changes in the current working directory.
     *
     * @param string $message
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Process
     */
    protected function commitChanges(string $message, InputInterface $input, OutputInterface $output): Process
    {
        $commands = [
            'git add .',
            "git commit -q -m \"$message\"",
            'git pull',
            'git push',
        ];

        return $this->runCommands($commands, $input, $output);
    }
}
