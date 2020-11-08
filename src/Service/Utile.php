<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Utile
{

  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function generateUniqueSlug($nom, $entity)
  {
    $slugger = new AsciiSlugger();
    $slug = $slugger->slug($nom);

    $verification = $this->em->getRepository('App\\Entity\\' . $entity)->findOneBySlug($slug);
    if ($verification != null) {
      $slug .= '-' . uniqid();
    }

    return $slug;
  }

  public function sendNotifications($entity)
  {
    foreach ($entity->getCategorie()->getFollows() as $follow) {
      $notif = new Notification();
      $notif->setName($entity->getTitle());
      $notif->setDate(new \DateTime());
      $notif->setUser($follow);
      $this->em->persist($notif);
    }
  }
}
