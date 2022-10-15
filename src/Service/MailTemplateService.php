<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\MailTemplate;
use App\Exception\EnvoiMailException;
use App\Exception\InvalidImageMailTemplateException;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    private $urlFormulaire; 
    private $imagesMailTemplateChemin;
    private $validator; 
    public function __construct($urlFormulaire, $imagesMailTemplateChemin, ValidatorInterface $validator)
    {
        $this->urlFormulaire = $urlFormulaire;
        $this->imagesMailTemplateChemin = $imagesMailTemplateChemin;  
        $this->validator = $validator; 
    }

    public function getMailTemplateCandidat(MailTemplate $mailTemplate, Candidat $candidat){

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
        // $contenuMail = str_replace($this::LIEN_VERS_LE_FORMULAIRE, $this->urlFormulaire . $candidat->getLien()->getToken(), $contenuMail); 
        
        return $contenuMail; 
    }

    public function enregistreImageTemplate(UploadedFile $image)
    {
        $nomFichier = $image->getClientOriginalName();
        if (count(explode('.',$nomFichier)) == 2) {
            $nbImagesMailTemplate = count(scandir($this->imagesMailTemplateChemin)) ; 
            $nombreAleatoire = rand(0, 20); 
            $nomFichier = explode('.', $nomFichier)[0]; 
            $nomFichier .= strval($nbImagesMailTemplate) . strval($nombreAleatoire) .  '.' . $image->getClientOriginalExtension(); 

            $image->move(
                $this->imagesMailTemplateChemin,
                $nomFichier
            );
            return  $this::DEBUT_URL_IMAGES . $nomFichier;
        }else {
            throw (new InvalidImageMailTemplateException("Des contraintes du fichier n'ont pas été respectées : Le nom du fichier ne doit pas contenir plusieurs points '.' "));
        }
    }

    public function getAllImagesUrl()
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
            return new Exception("Erreur dans la suppression du fichier : " . $exception->getMessage()); 
        }

    }
}