<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $cours = $em->getRepository(Cours::class)->findAll();
        $articles = $em->getRepository(Article::class)->findBy(array(), array("date" => "DESC"), 5);
        return $this->render('home/index.html.twig', [
            'cours' => $cours,
            'articles' => $articles,
        ]);
    }
}
