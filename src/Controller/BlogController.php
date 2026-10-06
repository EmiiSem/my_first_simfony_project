<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog/{id}', name: 'blog_view')]
    public function index(Blog $blog): Response
    {
        $form = $this->createForm(
            CommentType::class,
            null,
            [
                'action' => $this->generateUrl(
                    'blog_add_comment',
                    ['blog' => $blog->getId()])
            ]);

        return $this->render(
            'default/blog.html.twig',
            ['blog' => $blog, 'form' => $form->createView()]
        );
    }
}
