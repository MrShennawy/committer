<?php

namespace MrShennawy\Committer\git;

use Symfony\Component\Process\Process;

use function Laravel\Prompts\{warning};

class Status
{

    private $files = [];
    private $statusLetters = [
        "??" => "Untracked",
        "M" => "modified",
        "A" => "added",
        "D" => "deleted",
        "R" => "renamed"
    ];

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

    private function handleStatusLetter($letters)
    {
        if ($letters == '??') return $letters;

        $handled = '';
        for ($i = 0, $length = strlen($letters); $i < $length; $i++)
            $handled .= "$letters[$i] + ";

        return rtrim($handled, "+ ");
    }

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