<?php

namespace App\Command;

use App\Service\NewsGrabber;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'blog:news:import',
    description: 'Import news from Internet',
)]
class CrawlerCommand extends Command
{
    public function __construct(private readonly NewsGrabber $newsGrabber)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('count', InputArgument::OPTIONAL, 'Number of news')
            ->addOption('dryRun', null, InputOption::VALUE_NONE, 'Parse without saving to DB')
        ;
    }

    /**
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countArgument = $input->getArgument('count');
        $count = null !== $countArgument && '' !== $countArgument
            ? (int) $countArgument
            : null;
        $dryRun = (bool) $input->getOption('dryRun');

        $logger = new ConsoleLogger($output);

        $this->newsGrabber->setLogger($logger)->importNews($count, $dryRun);

        return Command::SUCCESS;
    }
}
