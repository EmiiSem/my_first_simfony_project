<?php
namespace App\Service;

use App\Entity\Blog;
use App\Repository\BlogRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;

class NewsGrabber
{
    private LoggerInterface $logger;
    private string $sslCertFile;
    private ?Client $httpClient = null;

    public function __construct(
        string $sslCertFile,
        private readonly UserRepository $userRepository,
        private readonly BlogRepository $blogRepository,
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        $this->sslCertFile = $sslCertFile;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setHttpClient(Client $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function importNews(?int $count = null, bool $dryRun = false): void
    {
        $this->logger->info('Start getting news');

        $client = $this->httpClient ?? new Client([
            'timeout' => 15.0,
            'verify' => $this->sslCertFile ?: true,
            'http_errors' => false,
            // Engadget часто режет browser-like UA; без UA/с дефолтным Guzzle проходит стабильнее
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);

        $listUrl = 'https://www.engadget.com/category/news/';
        $response = $client->get($listUrl);
        $status = $response->getStatusCode();
        $html = (string) $response->getBody();

        if ($status >= 400 || $html === '') {
            $this->logger->error(sprintf(
                'Failed to fetch news list: HTTP %d, body length %d',
                $status,
                strlen($html)
            ));

            return;
        }

        $texts = [];
        $crawler = new Crawler($html);
        $crawler->filter('h3 > a')->each(function (Crawler $node) use (&$texts, $count) {
            if (null !== $count && count($texts) >= $count) {
                return;
            }

            $href = $node->attr('href');
            $title = trim($node->text(''));
            if (!$href || $title === '') {
                return;
            }

            $texts[] = [
                'title' => $title,
                'href' => $href,
            ];
        });

        unset($crawler);

        $this->logger->info(sprintf('Get %d texts', count($texts)));

        if (0 === count($texts)) {
            $pageTitle = (new Crawler($html))->filter('title')->count()
                ? (new Crawler($html))->filter('title')->text('')
                : '-';
            $this->logger->warning(sprintf(
                'No news found by selector "h3 > a". HTTP %d, page title: "%s". Engadget could be blocking the request or changed markup.',
                $status,
                $pageTitle
            ));

            return;
        }

        foreach ($texts as &$text) {
            $articleUrl = str_starts_with($text['href'], 'http')
                ? $text['href']
                : 'https://www.engadget.com' . $text['href'];

            $response = $client->get($articleUrl);
            if ($response->getStatusCode() >= 400) {
                $this->logger->warning(sprintf(
                    'Skip "%s": HTTP %d for %s',
                    $text['title'],
                    $response->getStatusCode(),
                    $articleUrl
                ));
                $text['text'] = '';
                continue;
            }

            $crawler = new Crawler((string) $response->getBody());
            $crawlerBody = $crawler->filter('div.columns-holder > p');

            if (0 === $crawlerBody->count()) {
                $this->logger->warning(sprintf(
                    'Skip "%s": selector "div.columns-holder > p" not found',
                    $text['title']
                ));
                $text['text'] = '';
                continue;
            }

            $text['text'] = $crawlerBody->text();
            $this->logger->info(sprintf('Parsing news %s', $text['title']));
        }
        unset($text);

        $this->saveNews($texts, $dryRun);
    }

    private function saveNews(array $texts, bool $dryRun): void
    {
        $this->logger->info('Save news' . ($dryRun ? ' (dry-run, no DB writes)' : ''));

        $blogUser = $this->userRepository->find($this->parameterBag->get('autoblog'));
        if (!$blogUser) {
            $this->logger->error(sprintf('User %d is not found', $this->parameterBag->get('autoblog')));

            return;
        }

        foreach ($texts as $text) {
            if (($text['text'] ?? '') === '') {
                continue;
            }

            if ($this->blogRepository->getByTitle($text['title'])) {
                $this->logger->info(sprintf('News already exists: %s', $text['title']));
                continue;
            }

            if ($dryRun) {
                $this->logger->info(sprintf('Dry-run: would save blog "%s"', $text['title']));
                continue;
            }

            $this->logger->info(sprintf('Save blog %s', $text['title']));
            $blog = new Blog($blogUser);
            $blog
                ->setTitle($text['title'])
                ->setDescription(mb_substr($text['text'], 0, 200))
                ->setText($text['text'])
                ->setStatus('pending')
            ;
            $this->em->persist($blog);
        }

        if (!$dryRun) {
            $this->em->flush();
        }
    }
}
