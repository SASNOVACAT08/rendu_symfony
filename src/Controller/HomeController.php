<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/users", name="users")
     */
    public function users()
    {
        //on récupère les utilisateurs
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('home/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/panel", name="panel")
     */
    public function panel()
    {
        //On compte les stats, possible de le faire aussi à partir d'un query builder
        $em = $this->getDoctrine()->getManager();
        $articles = $em->getRepository(Article::class)->findAll();
        $cours = $em->getRepository(Cours::class)->findAll();
        $categories = $em->getRepository(Categorie::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        $articles_vue = 0;
        $cours_vue = 0;
        foreach ($articles as $article) {
            $articles_vue += $article->getVue();
        }
        foreach ($cours as $cour) {
            $cours_vue += $cour->getVue();
        }
        return $this->render('home/panel.html.twig', [
            'users' => $users,
            'categories' => $categories,
            'articles_vue' => $articles_vue,
            'cours_vue' => $cours_vue
        ]);
    }
}
