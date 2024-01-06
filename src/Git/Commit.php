<?php

namespace MrShennawy\Committer\Git;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\{alert, info, select, table, text};

class Commit
{
    /**
     * Define a constant array of commit types and their descriptions.
     *
     * The commit type is used to categorize and describe the purpose of a commit.
     * Each commit type has its own description which explains the purpose of the commit.
     * The descriptions are formatted using bold text to make them more noticeable.
     *
     * @var array COMMIT_TYPES An associative array of commit types and descriptions.
     */
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
     * Handles the commit process.
     *
     * @return string The commit sentence generated.
     */
    public function handle()
    {
        $type = $this->commitType();
        $sentence = $this->commitSentence();
        $issueId = $this->issueId();
        $commitSentence = "$type: $sentence".($issueId ? " - $issueId" : "");

        table(rows: [['Your commit =>', "<fg=green>$commitSentence</>"]]);
        return $commitSentence;
    }

    /**
     * Retrieves the desired commit type from the user.
     *
     * @return int|string The selected commit type.
     */
    private function commitType(): int|string
    {
        return select(label: "Select the desired commit type", options: self::COMMIT_TYPES, scroll: 10);
    }

    /**
     * Generates a commit sentence.
     *
     * @return string The commit sentence entered by the user.
     */
    private function commitSentence()
    {
        return text(
            label: "Enter the commit sentence",
            placeholder: 'Example: Fix creating users',
            required: true
        );
    }

    /**
     * Retrieves the issue ID from the user.
     *
     * @return string The issue ID entered by the user. If not specified, an empty string is returned.
     *
     * @see text() Helper function used to display a text input field.
     * @see text() The function being used in this method.
     */
    private function issueId()
    {
        return text(
            label: "Enter the issue ID <fg=yellow>(Optional)</>",
            placeholder: 'Example: JJSW-214 - JJSW-215',
            hint: 'issue ID for Jira, GitLab ... etc'
        );
    }

}