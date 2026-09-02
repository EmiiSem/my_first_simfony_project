<?php

namespace App\DataFixtures;

use App\Entity\Blog;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@mail.ru');
        $user->setRoles(['ROLE_ADMIN']);

        $password = $this->hasher->hashPassword($user, 'pass');
        $user->setPassword($password);

        $manager->persist($user);


        for($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@gmail.com');

            $password = $this->hasher->hashPassword($user, 'testing_12345');
            $user->setPassword($password);

            $manager->persist($user);

            $users = [];

            for ($j = 0; $j < 50; $j++) {
                $blog = (new Blog($user))
                    ->setTitle('Blog Title' . $j)
                    ->setDescription('Blog description' . $j)
                    ->setText('Blog text' . $j)
                ;
                $manager->persist($blog);

                $users[] = $user;
            }
        }

        for ($j = 0; $j < 50; $j++) {
            shuffle($users);
            foreach ($users as $item) {
                $blog = (new Blog($item))
                    ->setTitle('Blog Title' . $j)
                    ->setDescription('Blog description' . $j)
                    ->setText('Blog text' . $j)
                ;
                $manager->persist($blog);

                $users[] = $item;
            }
        }

        $manager->flush();
    }
}
