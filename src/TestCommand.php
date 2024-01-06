<?php

namespace MrShennawy\Committer;

use MrShennawy\Committer\Git\Add;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Laravel\Prompts\{progress, multiselect};

class TestCommand extends Command
{
    use RunCommands;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('test')
            ->setDescription('Test different operations');
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

        // (new Add)->selectFiles($input, $output);
        // die;
        //
        // $users = [['id' => 1, 'name' => 'ali'],['id' => 1, 'name' => 'ali'],['id' => 1, 'name' => 'ali'],['id' => 1, 'name' => 'ali']];
        // $progress = progress(label: 'Updating users', steps: count($users));
        //
        // $progress->start();
        // foreach ($users as $user) {
        //     sleep(1);
        //
        //     $progress->advance();
        // }
        //
        // $progress->finish();
        //
        //
        // $output->writeln(json_encode($users));
        //
        // // $io = new SymfonyStyle($input, $output);
        // // $io->definitionList(['shen', 'ali']);

        return Command::SUCCESS;
    }
}