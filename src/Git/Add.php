<?php

namespace MrShennawy\Committer\Git;
use MrShennawy\Committer\Traits\RunCommands;
use function Laravel\Prompts\{
    multiselect,
    confirm
};

class Add
{
    use RunCommands;

    /**
     * Handle method
     * This method is responsible for handling files.
     *
     * @return string The selected files separated by a space, or '.' if no files are selected.
     */
    public function handle(): string
    {
        $files = (new Status)->handelFiles();
        return $this->selectFiles($files);
    }

    /**
     * Select files from the given options.
     *
     * @param array $files The list of files to choose from.
     *
     * @return string The selected files, separated by spaces.
     */
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
            default: false,
            no: 'Select files',
            hint: "Use the space bar to select Files!");

        if(!$addAll)
            $this->selectFiles($files);

        return '.';
    }

}