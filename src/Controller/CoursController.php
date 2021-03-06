<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Service\Utile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cours")
 */
class CoursController extends AbstractController
{
    /**
     * @Route("/", name="cours_index", methods={"GET"})
     */
    public function index(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="cours_new", methods={"GET","POST"})
     */
    public function new(Request $request, Utile $utile, TranslatorInterface $translator): Response
    {
        $cour = new Cours();
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //On ajoute un cours avec un slug et un compteur de vu  à 0
            //On envoie une notif
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $utile->generateUniqueSlug($cour->getTitle(), 'Cours');
            $cour->setSlug($slug);
            $cour->setVue(0);
            $entityManager->persist($cour);
            $utile->sendNotifications($cour);
            $entityManager->flush();
            $this->addFlash("success", $translator->trans("flash.cours.add"));
            return $this->redirectToRoute('cours_index');
        }

        return $this->render('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="cours_show", methods={"GET"})
     */
    public function show(Cours $cour = null, TranslatorInterface $translator): Response
    {
        if ($cour == null) {
            $this->addFlash("danger", $translator->trans("flash.cours.non"));
            return $this->redirectToRoute("cours_index");
        }
        //On incrémente le compteur de vu
        $em = $this->getDoctrine()->getManager();
        $cour->setVue($cour->getVue() + 1);
        $em->flush();
        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}/edit", name="cours_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Cours $cour = null, TranslatorInterface $translator): Response
    {
        if ($cour == null) {
            $this->addFlash("danger", $translator->trans("flash.cours.non"));
            return $this->redirectToRoute("cours_index");
        }
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on modifie le cours
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", $translator->trans("flash.cours.update"));
            return $this->redirectToRoute('cours_index');
        }

        return $this->render('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/{slug}", name="cours_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Cours $cour = null, TranslatorInterface $translator): Response
    {
        if ($cour == null) {
            $this->addFlash("danger", $translator->trans("flash.cours.non"));
            return $this->redirectToRoute("cours_index");
        }
        if ($this->isCsrfTokenValid('delete' . $cour->getId(), $request->request->get('_token'))) {
            //on supprime le cours
            $entityManager = $this->getDoctrine()->getManager();
            $this->addFlash("success", $translator->trans("flash.cours.delete"));
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cours_index');
    }
}
