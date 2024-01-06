<?php

namespace MrShennawy\Committer\Git;

use Symfony\Component\Process\Process;

use function Laravel\Prompts\{warning};

class Status
{

    /**
     * An array that stores files.
     *
     * @var array
     */
    private $files = [];
    /**
     * Declaration of an associative array mapping status letters to their corresponding descriptions.
     *
     * The array maps the following status letters to their descriptions:
     * - "??" maps to "Untracked"
     * - "M" maps to "modified"
     * - "A" maps to "added"
     * - "D" maps to "deleted"
     * - "R" maps to "renamed"
     *
     * @var array $statusLetters
     */
    private $statusLetters = [
        "??" => "Untracked",
        "M" => "modified",
        "A" => "added",
        "D" => "deleted",
        "R" => "renamed"
    ];

    /**
     * Executes the 'git status -s' command and retrieves the list of modified files.
     *
     * @return array Returns an array of modified files.
     */
    public function command()
    {
        $process = new Process(['git', 'status', '-s']);

        $process->run();

        $output = trim($process->getOutput());
        $files = explode("\n", $output);
        if ($files[0] == "") {
            warning(message: 'You have no files to commit!');
            die;
        }
        return $files;
    }

    /**
     * Handles files and processes their statuses.
     *
     * @return array Associative array of files with their processed statuses.
     */
    public function handelFiles()
    {
        $files = $this->command();

        foreach ($files as $file) {
            $lineTrim = trim($file);
            $lineArr = explode(" ", $lineTrim);
            $statusLetters = $lineArr[0];

            $letters = $this->handleStatusLetter($statusLetters);
            $status = str_replace(array_keys($this->statusLetters), array_values($this->statusLetters), $letters);

            unset($lineArr[0]);
            $fileKey = $lineArr;
            if (in_array('->', $fileKey)) unset($fileKey[1], $fileKey[2]);

            $this->files[implode(' ', $fileKey)] = "<options=bold;fg={$this->statusColor($status)}>{$status}: </>" . implode(' ', $lineArr);
        }
        return $this->files;
    }

    /**
     * Handles the status letters.
     *
     * @param string $letters The status letters to be handled.
     * @return string The handled status letters.
     */
    private function handleStatusLetter($letters)
    {
        if ($letters == '??') return $letters;

        $handled = '';
        for ($i = 0, $length = strlen($letters); $i < $length; $i++)
            $handled .= "$letters[$i] + ";

        return rtrim($handled, "+ ");
    }

    /**
     * Determines the color corresponding to a given status.
     *
     * @param string $status The status to determine the color for.
     * @return string The color corresponding to the given status. Possible values are 'red', 'yellow', 'cyan', 'blue', or 'green'.
     */
    public function statusColor($status): string
    {
        return match ($status) {
            'Untracked', 'deleted' => 'red',
            'modified' => 'yellow',
            'added' => 'cyan',
            'renamed' => 'blue',
            default => 'green',
        };
    }
}