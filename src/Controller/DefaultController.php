<?php

namespace App\Controller;

use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'project_default')]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('default/index.html.twig', ['blogs' => $blogRepository->getBlogs()]);
    }
}
