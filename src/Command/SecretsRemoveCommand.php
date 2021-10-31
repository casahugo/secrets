<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CasaHugo\Secrets\Command;

use CasaHugo\Secrets\AbstractVault;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class SecretsRemoveCommand extends Command
{
    protected static $defaultName = 'remove';

    private $vault;
    private $localVault;

    public function __construct(AbstractVault $vault, AbstractVault $localVault = null)
    {
        $this->vault = $vault;
        $this->localVault = $localVault;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Remove a secret from the vault')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the secret')
            ->addOption('local', 'l', InputOption::VALUE_NONE, 'Update the local vault.')
            ->addOption('dir', 'd', InputOption::VALUE_REQUIRED, 'Target directory')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command removes a secret from the vault.

    <info>%command.full_name% <name></info>
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);
        $vault = $input->getOption('local') ? $this->localVault : $this->vault;

        if (null === $vault) {
            $io->success('The local vault is disabled.');

            return 1;
        }

        if ($vault->remove($name = $input->getArgument('name'))) {
            $io->success($vault->getLastMessage() ?? 'Secret was removed from the vault.');
        } else {
            $io->comment($vault->getLastMessage() ?? 'Secret was not found in the vault.');
        }

        if ($this->vault === $vault && null !== $this->localVault->reveal($name)) {
            $io->comment('Note that this secret is overridden in the local vault.');
        }

        return 0;
    }
}
