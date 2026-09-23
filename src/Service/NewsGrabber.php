<?php
namespace App\Service;

use App\Entity\Blog;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class NewsGrabber
{
    private string $sslCertFile;

    public function __construct(
        string $sslCertFile,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
    )
    {
        $this->sslCertFile = $sslCertFile;
    }

    /**
     * @throws GuzzleException
     */
    public function importNews(): void
    {
        $client = new Client([
            'timeout'  => 15.0,
            'verify' => $this->sslCertFile ?: true,
        ]);

        $response = $client->get('https://www.engadget.com/category/news/');

        $news = [];
        $crawler = new Crawler($response->getBody()->getContents());
        $crawler->filter('h3 > a')->each(function (Crawler $crawler) use (&$news) {
            $news[] = [
                'title' => $crawler->text(),
                'href' => $crawler->attr('href'),
            ];
        });

        unset($crawler);

        foreach ($news as &$item) {
            $response = $client->get('https://www.engadget.com' . $item['href']);
            $crawler = new Crawler($response->getBody()->getContents());

            $crawlerBody = $crawler->filter('div.columns-holder > p');

            $item['text'] = $crawlerBody->text();
        }
        unset($item);

        $blogUser = $this->userRepository->find(240);

        foreach ($news as $item) {
            $blog = new Blog($blogUser);
            $blog
                ->setTitle($item['title'])
                ->setDescription(mb_substr($item['text'], 0, 200))
                ->setText($item['text'])
                ->setStatus('pending')
            ;
            $this->em->persist($blog);
        }
        $this->em->flush();
    }
}
