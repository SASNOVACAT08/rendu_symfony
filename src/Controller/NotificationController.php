<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Form\NotificationType;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/", name="notification_index", methods={"GET"})
     */
    public function index(NotificationRepository $notificationRepository): Response
    {
        //on affiche les notifs
        $notifications = $this->getUser()->getNotifications();
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }


    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{id}", name="notification_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Notification $notification, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete' . $notification->getId(), $request->request->get('_token'))) {
            //on supprime la notif
            $entityManager = $this->getDoctrine()->getManager();
            $this->addFlash("success", $translator->trans("flash.notification.delete"));
            $entityManager->remove($notification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('notification_index');
    }
}
