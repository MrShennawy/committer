<?php

namespace MrShennawy\Committer\git;
use MrShennawy\Committer\Traits\RunCommands;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\{
    multiselect,
    info,
    confirm
};

class Add
{
    use RunCommands;

    public function handle(): string
    {
        $files = (new Status)->handelFiles();
        return $this->selectFiles($files);
    }

    private function selectFiles($files): string
    {
        $selectedFiles = multiselect(
            label: 'Select The files',
            options: $files
        );

        if(count($selectedFiles))
            return implode(' ', $selectedFiles);

        $addAll = confirm(
            label: 'You will add all files, Are you sure?',
            no: 'Select files',
            hint: "Use the space bar to select Files!");

        if(!$addAll)
            $this->selectFiles($files);

        return '.';
    }

}