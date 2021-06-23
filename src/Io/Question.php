<?php

/*
 * This file is part of the WP Starter package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace WeCodeMore\WpStarter\Io;

class Question
{
    /**
     * @var array<string>
     */
    private $lines = [];

    /**
     * @var array<string, string>
     */
    private $answers = [];

    /**
     * @var string
     */
    private $default = '';

    /**
     * @var array<string>|null
     */
    private $question;

    /**
     * @param array<string> $lines
     * @param array<string, string> $answers
     * @param string|null $default
     */
    public function __construct(array $lines, array $answers = [], string $default = null)
    {
        $this->lines = array_filter(
            $lines,
            static function (string $line): bool {
                return (bool)trim($line);
            }
        );

        if (!$this->lines) {
            return;
        }

        $validAnswers = array_filter(
            $answers,
            static function (string $value, string $key): bool {
                return trim($value) && trim($key);
            },
            ARRAY_FILTER_USE_BOTH
        );

        if (!$validAnswers) {
            return;
        }

        $validAnswers = array_change_key_case($validAnswers, CASE_LOWER);
        $answerKeys = array_map('trim', array_keys($validAnswers));

        $this->answers = array_combine($answerKeys, array_values($validAnswers));

        if ($default !== null) {
            $default = strtolower(trim($default));
            array_key_exists($default, $this->answers) or $default = null;
        }

        $this->default = $default ?? $answerKeys[0];
    }

    /**
     * @param string $answer
     * @return bool
     */
    public function isValidAnswer(string $answer): bool
    {
        return array_key_exists(strtolower(trim($answer)), $this->answers);
    }

    /**
     * @return string
     */
    public function defaultAnswerKey(): string
    {
        return $this->default;
    }

    /**
     * @return string
     */
    public function defaultAnswerText(): string
    {
        return $this->default ? $this->answers[$this->default] : '';
    }

    /**
     * @return array<string>
     *
     * @psalm-assert array<string> $this->question
     */
    public function questionLines(): array
    {
        if (is_array($this->question)) {
            return $this->question;
        }

        if (!$this->lines || !$this->answers) {
            $this->question = [];

            return [];
        }

        $this->question = array_values($this->lines);
        array_unshift($this->question, 'QUESTION:');
        $this->question[] = "";
        $this->question[] = implode(' | ', $this->answers);
        $this->default and $this->question[] = "Default: '{$this->default}'";

        return $this->question;
    }
}
