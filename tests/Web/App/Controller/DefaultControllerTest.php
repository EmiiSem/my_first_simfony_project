<?php

namespace App\Tests\Web\App\Controller;

use App\Factory\BlogFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DefaultControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    public function testSomething(): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne();
        BlogFactory::createMany(7, ['user' => $user]);

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello, world!');
        $this->assertCount(6, $crawler->filter('div.row > div'));
    }
}
