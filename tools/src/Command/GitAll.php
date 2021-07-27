<?php

/**
 * PHP Version 7.3
 *
 * Git All File
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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * One git to rule them all
 */
class GitAll extends Command
{
    /**
     * Command Name to be used
     * @var string
     */
    protected static $defaultName = 'git-all';

    /**
     * Accepted git commands
     */
    protected function configure()
    {
        $this->setDescription('One git to rule them all')
            ->addArgument('git_command', InputArgument::IS_ARRAY)
            ->addOption('force', null, InputOption::VALUE_NONE)
            ->addOption('tags', null, InputOption::VALUE_NONE)
            ->addOption('no-commit', null, InputOption::VALUE_NONE)
            ->addOption('abort', null, InputOption::VALUE_NONE)
            ->addOption('hard', null, InputOption::VALUE_NONE);
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

        foreach (glob('{*/.git,*/*/.git}', GLOB_ONLYDIR | GLOB_BRACE) as $gitDir) {
            $options = array_map(function ($item) {
                return "--{$item}";
            }, array_keys(array_filter($input->getOptions())));

            $commandLine = 'git'
                . ' ' . implode(' ', $input->getArgument('git_command'))
                . ' ' . implode(' ', $options);

            $commands[dirname($gitDir)] = new CommandRunner($output->section(), $commandLine, dirname($gitDir));
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
