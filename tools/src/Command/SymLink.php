<?php

/**
 * PHP Version 7.3
 *
 * Symbolic Link File
 *
 * @category Tools
 * @package  Breier\Tools
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3
 */

declare(strict_types=1);

namespace Breier\Tools\Command;

use Breier\Tools\Service\CommandRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A tool to replace a given vendor (composer) dependency with a symbolic link
 */
class SymLink extends Command
{
    /**
     * Command Name to be used
     * @var string
     */
    protected static $defaultName = 'sym-link';

    /**
     * Configuration
     */
    protected function configure()
    {
        $this->setDescription('A tool to replace a given vendor (composer) dependency with a symbolic link')
            ->addArgument('vendor', InputArgument::REQUIRED);
    }

    /**
     * Runs the job
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $commands = [];
        $vendor = $input->getArgument('vendor');

        foreach (glob("{*/vendor/$vendor/*,*/*/vendor/$vendor/*}", GLOB_ONLYDIR | GLOB_BRACE) as $composerDir) {
            $destDir = preg_replace('/[a-z\-0-9]+/', '..', dirname($composerDir))
                . '/' . basename($composerDir);

            $commandLine = file_exists(dirname($composerDir) . "/{$destDir}")
                ? 'rm -rf ' . basename($composerDir) . " && ln -s {$destDir}"
                : "echo '{$composerDir}' not found at the base of the project!";

            $commands[dirname($composerDir)] = new CommandRunner(
                $output->section(),
                $commandLine,
                dirname($composerDir)
            );
        }

        do {
            foreach ($commands as $name => $instance) {
                if (!$instance->updateCommand()) {
                    unset($commands[$name]);
                }
            }
        } while (!empty($commands));

        return self::SUCCESS;
    }
}
