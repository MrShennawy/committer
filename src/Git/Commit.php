<?php

namespace MrShennawy\Committer\Git;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\{alert, info, select, table, text};

class Commit
{
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

    public function handle()
    {
        $type = $this->commitType();
        $sentence = $this->commitSentence();
        $issueId = $this->issueId();
        $commitSentence = "$type: $sentence".($issueId ? " - $issueId" : "");

        table(rows: [['Your commit =>', "<fg=green>$commitSentence</>"]]);
        return $commitSentence;
    }

    private function commitType(): int|string
    {
        return select(label: "Select the desired commit type", options: self::COMMIT_TYPES, scroll: 10);
    }

    private function commitSentence()
    {
        return text(
            label: "Enter the commit sentence",
            placeholder: 'Example: Fix creating users',
            required: true
        );
    }

    private function issueId()
    {
        return text(
            label: "Enter the issue ID <fg=yellow>(Optional)</>",
            placeholder: 'Example: JJSW-214 - JJSW-215',
            hint: 'issue ID for Jira, GitLab ... etc'
        );
    }

}