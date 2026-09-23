<?php

namespace App\Command;

use App\Service\NewsGrabber;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
    }

    /**
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->newsGrabber->importNews();

        return Command::SUCCESS;
    }
}
