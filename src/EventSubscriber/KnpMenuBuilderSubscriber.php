<?php
// src/EventSubscriber/KnpMenuBuilderSubscriber.php
namespace App\EventSubscriber;

use KevinPapst\AdminLTEBundle\Event\KnpMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KnpMenuBuilderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KnpMenuEvent::class => ['onSetupMenu', 100],
        ];
    }
    
    public function onSetupMenu(KnpMenuEvent $event)
    {
        $menu = $event->getMenu();


        $menu->addChild("Accueil", [
            'label' => "Accueil",
            "route" => "app_accueil",
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-home');

        $menu->addChild("Candidats", [
            'label' => "Candidats",
            "route" => "app_candidat_index",
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-users');

        $menu->addChild("Types de contrats", [
            'label' => "Types de contrat",
            "route" => "app_type_candidat_index",
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fas fa-file-signature');

        $menu->addChild("Types de documents", [
            'label' => "Types de documents",
            "route" => "app_type_document_index",
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-file');



        $menu->addChild("Administration", [
            'label' => "Administration",
            'childOptions' => $event->getChildOptions()
        ])->setAttribute('class', 'header');
        $menu->addChild("Paramétrage", [
            'label' => "Paramétrage",
            "route" => "app_administration",
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-cogs');
    }
}