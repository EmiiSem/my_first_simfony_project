<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'project_default')]
    public function index(BlogRepository $blogRepository,  EntityManagerInterface $em): Response
    {
        $blog = $blogRepository->findOneBy(['id' => 2]);
        //$em->remove($blog); // remove - запрос на удаление
        $em->refresh($blog); // refresh - получение свежих данных
        $em->flush(); // flush - зафексировать изменения
        dump($blog);
        exit;

        $blog = (new Blog())
                ->setTitle('My First Blog')
                ->setDescription('This is a description.')
                ->setText('This is a text.')
        ;

        $em->persist($blog); // persist - запрос на добавление
        $em->flush(); // flush - зафексировать изменения
        exit;

        return $this->render('index.html.twig', []);
    }
}
