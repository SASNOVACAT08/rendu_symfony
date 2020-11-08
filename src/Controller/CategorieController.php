<?php

namespace App\Controller;


use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Service\Utile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/categorie")
 */
class CategorieController extends AbstractController
{
    /**
     * @Route("/", name="categorie_index", methods={"GET"})
     */
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="categorie_new", methods={"GET","POST"})
     */
    public function new(Request $request, Utile $utile, TranslatorInterface $translator): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $utile->generateUniqueSlug($categorie->getName(), 'Categorie');
            $categorie->setSlug($slug);
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash("success", $translator->trans("flash.categorie.add"));
            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="categorie_show", methods={"GET"})
     */
    public function show(Categorie $categorie = null, TranslatorInterface $translator): Response
    {
        if ($categorie == null) {
            $this->addFlash("danger", $translator->trans("flash.categorie.non"));
            return $this->redirectToRoute("categorie_index");
        }
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}/edit", name="categorie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Categorie $categorie = null, TranslatorInterface $translator): Response
    {
        if ($categorie == null) {
            $this->addFlash("danger", $translator->trans("flash.categorie.non"));
            return $this->redirectToRoute("categorie_index");
        }
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", $translator->trans("flash.categorie.update"));
            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}", name="categorie_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Categorie $categorie = null, TranslatorInterface $translator): Response
    {
        if ($categorie == null) {
            $this->addFlash("danger", $translator->trans("flash.categorie.non"));
            return $this->redirectToRoute("categorie_index");
        }
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $this->addFlash("success", $translator->trans("flash.categorie.delete"));
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('categorie_index');
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{slug}/follow", name="categorie_follow", methods={"GET"})
     */
    public function follow(Categorie $categorie = null, TranslatorInterface $translator): Response
    {
        if ($categorie == null) {
            $this->addFlash("danger", $translator->trans("flash.categorie.non"));
            return $this->redirectToRoute("categorie_index");
        }
        $em = $this->getDoctrine()->getManager();
        $this->getUser()->addCategory($categorie);
        $categorie->addFollow($this->getUser());
        $this->addFlash("success", $translator->trans("flash.categorie.follow"));
        $em->flush();
        return $this->redirectToRoute('categorie_index');
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{slug}/unfollow", name="categorie_unfollow", methods={"GET"})
     */
    public function unfollow(Categorie $categorie = null, TranslatorInterface $translator): Response
    {
        if ($categorie == null) {
            $this->addFlash("danger", $translator->trans("flash.categorie.non"));
            return $this->redirectToRoute("categorie_index");
        }
        $em = $this->getDoctrine()->getManager();
        $this->getUser()->removeCategory($categorie);
        $categorie->removeFollow($this->getUser());
        $this->addFlash("success", $translator->trans("flash.categorie.unfollow"));
        $em->flush();
        return $this->redirectToRoute('categorie_index');
    }
}
