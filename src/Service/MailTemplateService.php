<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\MailEnvoye;
use App\Entity\MailTemplate;
use App\Entity\PieceJointe;
use App\Entity\User;
use App\Exception\EnvoiMailException;
use App\Exception\InvalidFileMailTemplateException;
use App\Repository\PieceJointeRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailTemplateService
{   
    public const NOM_ENTREPRISE = "&lt;&lt;NOM_ENTREPRISE&gt;&gt;"; 
    public const POSTE = "&lt;&lt;POSTE&gt;&gt;"; 
    public const TYPE_CONTRAT = "&lt;&lt;TYPE_CONTRAT&gt;&gt;"; 
    public const LIEN_VERS_LE_FORMULAIRE = "&lt;&lt;LIEN_VERS_LE_FORMULAIRE&gt;&gt;"; 
    public const LISTE_DOCUMENTS = "&lt;&lt;LISTE_DOCUMENTS&gt;&gt;"; 

    public const LISTE_VARIABLES = [
        "<<NOM_ENTREPRISE>>", 
        "<<POSTE>>", 
        "<<TYPE_CONTRAT>>", 
        "<<LIEN_VERS_LE_FORMULAIRE>>", 
        "<<LISTE_DOCUMENTS>>", 
    ];

    public const DEBUT_URL_IMAGES = "http://localhost:8081/images_mail_template/"; 
    public const DEBUT_URL_PJ = "http://localhost:8081/pj_mail_template/"; 

    private $urlFormulaire; 
    private $imagesMailTemplateChemin;
    private $pjMailTemplateChemin;
    private $validator; 
    private $entityManager; 
    private $pieceJointeRepository;
    private $loginLinkHandler; 
    private $mailer; 
    private $hasher;
    private $userRepository;
    public function __construct($urlFormulaire, $imagesMailTemplateChemin,$pjMailTemplateChemin, 
                                ValidatorInterface $validator, 
                                EntityManagerInterface $entityManager, 
                                PieceJointeRepository $pieceJointeRepository, 
                                LoginLinkHandlerInterface $loginLinkHandler, 
                                MailerInterface $mailer, 
                                UserPasswordHasherInterface $hasher,
                                UserRepository $userRepository)
    {
        $this->urlFormulaire = $urlFormulaire;
        $this->imagesMailTemplateChemin = $imagesMailTemplateChemin;  
        $this->validator = $validator;
        $this->pjMailTemplateChemin = $pjMailTemplateChemin;  
        $this->entityManager = $entityManager; 
        $this->pieceJointeRepository = $pieceJointeRepository; 
        $this->loginLinkHandler = $loginLinkHandler; 
        $this->mailer = $mailer; 
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
    }

    public function getMailTemplateCandidat(MailTemplate $mailTemplate, Candidat $candidat): string
    {

        $errors = []; 
        if ($candidat->getPoste() == null) {
            $errors["Candidat"] = "Poste"; 
        }
        if ($candidat->getTypeCandidat() == null) {
            $errors["Candidat"] = "Type de contrat"; 
        }
        // if ( $candidat->getTypeCandidat()->getDocumentsAFournir()->isEmpty()) {
        //     $errors["Type de contrat"] = "Type de contrat -> ". $candidat->getTypeCandidat()->getLibelle() ." aucun type de document pour ce type de contrat"; 
        // }

        if (!empty($errors)) {
            throw (new EnvoiMailException())
                    ->setAttributsManquants($errors); 
        }

        $contenuMail = $mailTemplate->getContenu(); 
        $contenuMail = str_replace($this::NOM_ENTREPRISE, "Maraz entreprise", $contenuMail); 
        $contenuMail = str_replace($this::POSTE, $candidat->getPoste(), $contenuMail); 
        $contenuMail = str_replace($this::TYPE_CONTRAT, $candidat->getTypeCandidat()->getLibelle(), $contenuMail); 
        if ($candidat->getTypeCandidat()->getDocumentsAFournir()->isEmpty()) {
            $documentsAFournir = "Aucun document à fournir"; 
        }else {
            $documentsAFournir = $candidat->getTypeCandidat()->__toStringDocumentsPourMail(); 
        }
        $contenuMail = str_replace($this::LISTE_DOCUMENTS,$documentsAFournir , $contenuMail); 

        if (!$this->userRepository->findOneBy(["email" => $candidat->getEmail()])) {
            $user = (new User())
                    ->setEmail($candidat->getEmail()); 
            $user->setPassword($this->hasher->hashPassword($user, $candidat->getMdpGenere())); 
            $user->setRoles(["ROLE_CANDIDAT"]); 

            $this->entityManager->persist($user); 
            $this->entityManager->flush(); 
        }

        $user = $this->userRepository->findOneBy(["email" => $candidat->getEmail()]); 

        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);

        $contenuMail = str_replace($this::LIEN_VERS_LE_FORMULAIRE, "<a href='".$loginLinkDetails->getUrl()."' >". $loginLinkDetails->getUrl() ."</a>", $contenuMail); 
        
        return $contenuMail; 
    }

    public function genereNomFichier(string $nomFichier, UploadedFile $fichier):string
    {
        $nbImagesMailTemplate = count(scandir($this->imagesMailTemplateChemin)) ; 
        $nombreAleatoire = rand(0, 20); 
        $nomFichier = explode('.', $nomFichier)[0]; 
        $nomFichier .= strval($nbImagesMailTemplate) . strval($nombreAleatoire) .  '.' . $fichier->getClientOriginalExtension(); 
        return $nomFichier; 
    }

    public function enregistreImageTemplate(UploadedFile $image): string
    {
        $nomFichier = $image->getClientOriginalName();
        if (count(explode('.',$nomFichier)) == 2) {
            $nomFichier = $this->genereNomFichier($nomFichier, $image); 

            $image->move(
                $this->imagesMailTemplateChemin,
                $nomFichier
            );
            return $this::DEBUT_URL_IMAGES .  $nomFichier;
        }else {
            throw (new InvalidFileMailTemplateException("Des contraintes du fichier n'ont pas été respectées : Le nom du fichier ne doit pas contenir plusieurs points '.' "));
        }
    }

    public function enregistrePJTemplate(MailTemplate $mailTemplate, UploadedFile $pj): PieceJointe
    {
        $nomFichier = $pj->getClientOriginalName();
        if (count(explode('.',$nomFichier)) == 2) {
            $nomFichier = $this->genereNomFichier($nomFichier, $pj); 

            $pieceJointe = (new PieceJointe())
                            ->setNom($nomFichier)
                            ->setChemin($this->pjMailTemplateChemin . '/' . $nomFichier)
                            ->setUrl($this::DEBUT_URL_PJ . $nomFichier)
                            ->setActif(true)
                            ->setMailTemplate($mailTemplate); 
            
            $mailTemplate->addPieceJointe($pieceJointe); 
            $this->entityManager->flush();  
            $pj->move(
                $this->pjMailTemplateChemin,
                $nomFichier
            );
            return $pieceJointe;
        }else {
            throw (new InvalidFileMailTemplateException("Des contraintes du fichier n'ont pas été respectées : Le nom du fichier ne doit pas contenir plusieurs points '.' "));
        }
    }

    public function getAllImagesUrl(): array
    {
        $files = array_filter(scandir($this->imagesMailTemplateChemin),function($value){
            return $value != '..' && $value != '.'; 
        }); 
        $fichiers = []; 
        foreach ($files as $value) {
            $fichiers[$value] = $this::DEBUT_URL_IMAGES . $value; 
        }
        return $fichiers; 
    }


    public function deleteImageByUrl($url)
    {
        try {
            $nomFichier = str_replace($this::DEBUT_URL_IMAGES, '', $url); 
            $filesystem = new Filesystem(); 
            if ($filesystem->exists($this->imagesMailTemplateChemin . '/' . $nomFichier)) {
                $filesystem->remove($this->imagesMailTemplateChemin . '/' . $nomFichier); 
            }
        } catch (IOException $exception) {
            throw new Exception("Erreur dans la suppression du fichier : " . $exception->getMessage()); 
        }

    }

    public function deletePJ(int $id)
    {
        try {
            $pieceJointe = $this->pieceJointeRepository->findOneBy(["id" => intval($id)]);  
            $filesystem = new Filesystem(); 
            if ($filesystem->exists($pieceJointe->getChemin())) {
                $filesystem->remove($pieceJointe->getChemin()); 
            }
            $this->pieceJointeRepository->remove($pieceJointe, true);
        } catch (IOException $exception) {
            throw new Exception("Erreur dans la suppression du fichier : " . $exception->getMessage()); 
        }

    }

    public function envoiLienCandidat(Candidat $candidat, MailTemplate $mailTemplate, FormInterface $mailForm)
    {
        $mailObjet = (new MailEnvoye())
        ->setSujet($mailForm->get("sujet")->getData())
        ->setContenu($mailForm->get("contenu")->getData())
        ->setCandidat($candidat); 

        foreach ($mailTemplate->getPieceJointes() as $pieceJointe) {
            if ($pieceJointe->isActif()) {
                $pieceJointe->addMailEnvoyesCandidat($mailObjet); 
            }
        }

        $email = (new Email())
            ->from('no-reply-formulaire@aurorh.com')
            ->to($candidat->getEmail())
            ->subject("NE PAS REPONDRE: FORMULAIRE D'EMBAUCHE")
            ->text($mailObjet->getContenu());

        foreach ($mailObjet->getPieceJointes() as $pieceJointe) {
            $email->attachFromPath($pieceJointe->getChemin(), $pieceJointe->getNom()); 
        }

        $this->mailer->send($email);
        $this->entityManager->persist($mailObjet); 
        $this->entityManager->flush();
    }

}