<?php

/**
 * PHP Version 7.3
 *
 * Command Runner File
 *
 * @category Tools
 * @package  Breier\Tools
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3
 */

declare(strict_types=1);

namespace Breier\Tools\Service;

use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * Command Runner Class
 */
class CommandRunner
{
    /** @var ConsoleSectionOutput */
    private $outputSection;

    /** @var string */
    private $basePath;

    /** @var resource */
    private $outputFile;

    /** @var int */
    private $pid;

    /**
     * Accepted git commands
     */
    public function __construct(
        ConsoleSectionOutput $consoleSectionOutput,
        string $commandLine,
        string $basePath = '.'
    ) {
        $this->outputSection = $consoleSectionOutput;
        $this->basePath = $basePath;

        $this->outputFile = tmpfile();

        $this->runCommand($commandLine);
    }

    /**
     * Update output
     *
     * @return bool command running
     */
    public function updateCommand(): bool
    {
        $isRunning = $this->isRunning();

        rewind($this->outputFile);
        $output = '';
        while ($more = fread($this->outputFile, 1024)) {
            $output .= rtrim($more, PHP_EOL);
        }

        if (empty($output)) {
            $output = '...';
        }

        $this->outputSection->overwrite("<info>### {$this->basePath} ###-></info> {$output}");

        return $isRunning;
    }

    /**
     * Run command
     */
    private function runCommand(string $command): void
    {
        $this->outputSection->overwrite("<info>### {$this->basePath} ###-></info> {$command}");

        $pidFile = tmpfile();

        exec(
            sprintf(
                "%s > %s 2>&1 & echo $! >> %s",
                "(cd {$this->basePath} && {$command})",
                stream_get_meta_data($this->outputFile)['uri'],
                stream_get_meta_data($pidFile)['uri']
            )
        );

        $this->pid = (int) fread($pidFile, 64);
    }

    /**
     * Check if command is still running
     */
    private function isRunning(): bool
    {
        $result = shell_exec(sprintf("ps %d", $this->pid));

        return (count(preg_split("/\n/", $result)) > 2);
    }
}
