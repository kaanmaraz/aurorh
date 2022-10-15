<?php

namespace App\Test\Service;

use App\Entity\Candidat;
use App\Entity\Lien;
use App\Entity\MailTemplate;
use App\Entity\TypeCandidat;
use App\Entity\TypeDocument;
use App\Exception\EnvoiMailException;
use App\Service\MailTemplateService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MailTemplateServiceTest extends KernelTestCase
{
    public function getTypeDocument(){
        return  (new TypeDocument())
                ->setLibelle("Carte d'identité")
                ->setFormat('pdf')
                ->setMultiple(true); 
    }

    public function getTypeCandidat(){
        return (new TypeCandidat())
                ->setLibelle("CDI"); 
    }

    public function getLien(){
        return (new Lien())
                ->setToken("AAAAAAAAAAAAAAA"); 
    }

    public function getCandidat()
    {
        return (new Candidat())
            ->setNom("TestPrenom")
            ->setPrenom("TestNom")
            ->setPoste("TestPoste"); 

    }

    public function getMailTemplate()
    {
        return (new MailTemplate())
                ->setContenu("<p>Bonjour,&nbsp;</p>

                <p>Nous avons le plaisir de vous annoncer votre recrutement au sein de l&#39;entreprise &lt;&lt;NOM_ENTREPRISE&gt;&gt; en tant que &lt;&lt;POSTE&gt;&gt; pour un &lt;&lt;TYPE_CONTRAT&gt;&gt;</p>
                
                <p>Afin que nous puissioon finaliser votre embauche nous avons besoin de certaines informations vous concernant ainsi que quelques document.&nbsp;</p>
                
                <p>Nous vous invitons donc &agrave; remplir le formulaire en cliquant sur le lien suivant:</p>
                
                <p>&lt;&lt;LIEN_VERS_LE_FORMULAIRE&gt;&gt;.&nbsp;</p>
                
                <p>Avant de vous lancer dans le formulaire veuillez r&eacute;unir les documents suivants:&nbsp;</p>
                
                <p>&lt;&lt;LISTE_DOCUMENTS&gt;&gt;.&nbsp;</p>
                
                <p>Cordialement,&nbsp;</p>"); 
    }

    public function testGetMailTemplateCandidatSuccessfull(){

        $typeCandidat = $this->getTypeCandidat()
                        ->addDocumentsAFournir($this->getTypeDocument());

        $candidat = $this->getCandidat()
                    ->setTypeCandidat($typeCandidat)
                    ->setLien($this->getLien()); 
        $mailTemplate = $this->getMailTemplate(); 
        
        /** @var MailTemplateService $mailTemplateService  */ 
        $mailTemplateService = $this->getContainer()->get("App\Service\MailTemplateService"); 
        $result = $mailTemplateService->getMailTemplateCandidat($mailTemplate,$candidat); 
        dump($result); 
    }

    public function testGetMailTemplateCandidatUnsuccessfull(){

        $typeCandidat = $this->getTypeCandidat();
                        // ->addDocumentsAFournir($this->getTypeDocument());

        $candidat = $this->getCandidat()
                    ->setTypeCandidat($typeCandidat)
                    ->setLien($this->getLien()); 
        $mailTemplate = $this->getMailTemplate(); 
        
        /** @var MailTemplateService $mailTemplateService  */ 
        $mailTemplateService = $this->getContainer()->get("App\Service\MailTemplateService"); 
        
        try {
            $mailTemplateService->getMailTemplateCandidat($mailTemplate,$candidat); 
        } catch (EnvoiMailException $envoiMailException) {
            $expectedErrorMessage = "Veuillez renseigner les champs suivants : Type de contrat -> ". $typeCandidat->getLibelle() ." aucun type de document pour ce type de contrat"; 
            $this->assertEquals($expectedErrorMessage,$envoiMailException->getMessage()); 
        }
        
    }

    public function testGetAllImagesUrl()
    {
        /** @var MailTemplateService $mailTemplateService  */ 
        $mailTemplateService = $this->getContainer()->get("App\Service\MailTemplateService"); 
        dump($mailTemplateService->getAllImagesUrl()); 
    }
}