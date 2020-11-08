<?php

namespace App\Controller;

use App\Service\Utile;
use App\Entity\Article;
use App\Entity\Notification;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request, Utile $utile, TranslatorInterface $translator): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $utile->generateUniqueSlug($article->getTitle(), 'Article');
            $article->setDate(new \DateTime());
            $article->setAuthor($this->getUser());
            $article->setSlug($slug);
            $entityManager->persist($article);
            foreach ($article->getCategorie()->getFollows() as $follow) {
                $notif = new Notification();
                $notif->setName($article->getTitle());
                $notif->setDate(new \DateTime());
                $notif->setUser($follow);
                $entityManager->persist($notif);
            }
            $entityManager->flush();
            $this->addFlash("success", $translator->trans("flash.article.add"));
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="article_show", methods={"GET"})
     */
    public function show(Article $article = null, TranslatorInterface $translator): Response
    {
        if ($article == null) {
            $this->addFlash("danger", $translator->trans("flash.article.non"));
            return $this->redirectToRoute("article_index");
        }
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}/edit", name="article_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Article $article = null, TranslatorInterface $translator): Response
    {
        if ($article == null) {
            $this->addFlash("danger", $translator->trans("flash.article.non"));
            return $this->redirectToRoute("article_index");
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", $translator->trans("flash.article.edit"));
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article = null, TranslatorInterface $translator): Response
    {
        if ($article == null) {
            $this->addFlash("danger", $translator->trans("flash.article.non"));
            return $this->redirectToRoute("article_index");
        }
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $this->addFlash("success", $translator->trans("flash.article.delete"));
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }
}
