<?php

namespace Tests\Kernel\Service;

use App\Factory\UserFactory;
use App\Repository\BlogRepository;
use App\Service\NewsGrabber;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class NewsGrabberTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testImportNewsPersistsBlog(): void
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        /** @var NewsGrabber $newsGrabber */
        $newsGrabber = static::getContainer()->get(NewsGrabber::class);
        /** @var BlogRepository $blogRepository */
        $blogRepository = static::getContainer()->get(BlogRepository::class);

        $newsGrabber->setHttpClient($this->createMockedHttpClient());

        $this->assertSame(0, $blogRepository->count([]));

        $newsGrabber->importNews(1, false);

        $this->assertSame(1, $blogRepository->count([]));

        $blog = $blogRepository->findOneBy(['title' => 'First imported news']);
        $this->assertNotNull($blog);
        $this->assertSame('pending', $blog->getStatus());
        $this->assertSame($user->getId(), $blog->getUser()?->getId());
        $this->assertStringContainsString(
            'This is the full body of the first imported news article',
            (string) $blog->getText()
        );
        $this->assertSame(
            mb_substr((string) $blog->getText(), 0, 200),
            $blog->getDescription()
        );
    }

    public function testImportNewsDryRunDoesNotPersistBlog(): void
    {
        self::bootKernel();

        UserFactory::createOne();

        /** @var NewsGrabber $newsGrabber */
        $newsGrabber = static::getContainer()->get(NewsGrabber::class);
        /** @var BlogRepository $blogRepository */
        $blogRepository = static::getContainer()->get(BlogRepository::class);

        $newsGrabber->setHttpClient($this->createMockedHttpClient());
        $newsGrabber->importNews(1, true);

        $this->assertSame(0, $blogRepository->count([]));
    }

    public function testImportNewsDoesNotDuplicateExistingBlog(): void
    {
        self::bootKernel();

        UserFactory::createOne();

        /** @var NewsGrabber $newsGrabber */
        $newsGrabber = static::getContainer()->get(NewsGrabber::class);
        /** @var BlogRepository $blogRepository */
        $blogRepository = static::getContainer()->get(BlogRepository::class);

        $newsGrabber->setHttpClient($this->createMockedHttpClient(imports: 2));
        $newsGrabber->importNews(1, false);

        $this->assertSame(1, $blogRepository->count([]));

        // второй импорт той же новости не должен создать дубликат
        $newsGrabber->setHttpClient($this->createMockedHttpClient(imports: 1));
        $newsGrabber->importNews(1, false);

        $this->assertSame(1, $blogRepository->count([]));
        $this->assertCount(1, $blogRepository->findBy(['title' => 'First imported news']));
    }

    private function createMockedHttpClient(int $imports = 1): Client
    {
        $listHtml = file_get_contents(dirname(__DIR__, 2).'/fixtures/engadget_list.html');
        $articleHtml = file_get_contents(dirname(__DIR__, 2).'/fixtures/engadget_article.html');

        $responses = [
            new Response(200, ['Content-Type' => 'text/html'], $listHtml),
        ];

        for ($i = 0; $i < $imports; ++$i) {
            $responses[] = new Response(200, ['Content-Type' => 'text/html'], $articleHtml);
        }

        $mock = new MockHandler($responses);

        return new Client(['handler' => HandlerStack::create($mock)]);
    }
}
