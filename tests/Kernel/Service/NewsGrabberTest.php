<?php

namespace Tests\Kernel\Service;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class NewsGrabberTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testSomething(): void
    {
        self::bootKernel();

        $user = UserFactory::createOne();


    }
}
