<?php

namespace App\Service;

use App\Entity\Candidat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;  

class GestionnaireExcel
{
    public $colonnes;
    private $fichierExcel;
    private $listeServicesChemin; 

    public function __construct($fichierExcel, $listeServicesChemin)
    {
        $this->colonnes = [
            'Civilité' => 'A',
            'Nom' => 'B',
            'Prénom' => 'C',
            'Numéro Agent' => 'D',
            'NINSEE' => 'E',
            'Date de naissance' => 'F',
            'Ville de naissance' => 'G',
            'Adresse' => 'H',
            'Code postal' => 'I',
            'Ville' => 'J',
            'Signature CDD' => 'K',
            'Début CDD' => 'L',
            'Fin CDD' => 'M',
            "Période d'essai" => 'N',
            'Lieu de travail' => 'O',
            'Adresse de travail' => 'P',
            'Emploi' => 'Q',
            'Service' => 'R',
            'Niveau de salaire' => 'S',
            'Coeff de base' => 'T',
            'Coeff developpé' => 'U',
            'Points de garantie' => 'V',
            'Gestionnaire' => 'W',
            'Initiale Gestionnaire' => 'X',
            'Points de compétence' => 'Y',
            "Points d'expérience" => 'Z',
            "Nationnalité" => 'AA',
        ];
        $this->fichierExcel = $fichierExcel;
        $this->listeServicesChemin = $listeServicesChemin; 
    }

    public function ajouterLigneCandidat(Candidat $candidat)
    {
        $spreadsheet = new Spreadsheet();
        $inputFileName = $this->fichierExcel;

        //Creer un nouveau lecteur xls
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        //Récupère la dernière ligne du fichier excel pour y ajouter le candida
        $ligneCandidat = strval($worksheet->getHighestRow() + 1);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach($this->colonnes as $colonne){
            $worksheet
            ->getStyle($colonne.$ligneCandidat)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('00000000'));
        }

        //Rempli chaque colonnes avec les infos correspondantes
        $worksheet->setCellValue($this->colonnes['Civilité'].$ligneCandidat, $candidat->getSexe());
        $worksheet->setCellValue($this->colonnes['Nom'].$ligneCandidat, $candidat->getNom());
        $worksheet->setCellValue($this->colonnes['Prénom'].$ligneCandidat, $candidat->getPrenom());
        $worksheet->setCellValue($this->colonnes['Numéro Agent'].$ligneCandidat, $candidat->getNumeroAgent());
        $worksheet->setCellValue($this->colonnes['NINSEE'].$ligneCandidat, $candidat->getNumeroSs());
        $worksheet->setCellValueExplicit(
            $this->colonnes['NINSEE'].$ligneCandidat,
            $candidat->getNumeroSs(),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $worksheet->setCellValue($this->colonnes['Date de naissance'].$ligneCandidat, $candidat->getDateDeNaissance()->format('d-m-Y'));
        $worksheet->setCellValue($this->colonnes['Ville de naissance'].$ligneCandidat, $candidat->getVilleNaissance());
        $worksheet->setCellValue($this->colonnes['Adresse'].$ligneCandidat, $candidat->getAdresse());
        $worksheet->setCellValue($this->colonnes['Code postal'].$ligneCandidat, $candidat->getCodePostal());
        $worksheet->setCellValue($this->colonnes['Ville'].$ligneCandidat, $candidat->getVille());
        // $worksheet->setCellValue( $this->colonnes["Signature CDD"] . $ligneCandidat, $candidat->getSexe());
        if (str_contains(strtoupper($candidat->getTypeCandidat()->getLibelle()), 'CDD')) {
            $worksheet->setCellValue($this->colonnes['Début CDD'].$ligneCandidat, $candidat->getDebutCDD()->format('d-m-Y'));
            $worksheet->setCellValue($this->colonnes['Fin CDD'].$ligneCandidat, $candidat->getFinCDD()->format('d-m-Y'));

        }
        $worksheet->setCellValue($this->colonnes["Période d'essai"].$ligneCandidat, $candidat->getPeriodeEssai());
        $worksheet->setCellValue($this->colonnes['Lieu de travail'].$ligneCandidat, $candidat->getSite());
        $worksheet->setCellValue($this->colonnes['Adresse de travail'].$ligneCandidat, $candidat->getSite());
        $worksheet->setCellValue($this->colonnes['Emploi'].$ligneCandidat, $candidat->getPoste());
        $worksheet->setCellValue($this->colonnes['Service'].$ligneCandidat, $candidat->getService());
        $worksheet->setCellValue($this->colonnes['Niveau de salaire'].$ligneCandidat, $candidat->getNiveauSalaire());
        $worksheet->setCellValue($this->colonnes['Coeff de base'].$ligneCandidat, $candidat->getCoeffBase());
        $worksheet->setCellValue($this->colonnes['Coeff developpé'].$ligneCandidat, $candidat->getCoeffDevpe());
        $worksheet->setCellValue($this->colonnes['Points de garantie'].$ligneCandidat, $candidat->getPtsGarantie());
        // $worksheet->setCellValue( $this->colonnes["Gestionnaire"] . $ligneCandidat, $candidat->getSexe());
        // $worksheet->setCellValue( $this->colonnes["Initiale Gestionnaire"] . $ligneCandidat, $candidat->getSexe());
        $worksheet->setCellValue($this->colonnes['Points de compétence'].$ligneCandidat, $candidat->getPtsCompetences());
        $worksheet->setCellValue($this->colonnes["Points d'expérience"].$ligneCandidat, $candidat->getPtsExperiences());
        $worksheet->setCellValue($this->colonnes["Nationnalité"].$ligneCandidat, $candidat->getNationnalite());



        $spreadsheet->getActiveSheet()->getStyle('A1:AA1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
        $spreadsheet->getActiveSheet()->getStyle('A1:AA1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $writer = new Xls($spreadsheet);
        $writer->save($inputFileName);
    }

    public function recupServices(){

        $tableauServices = []; 

        $spreadsheet = new Spreadsheet();
        $inputFileName = $this->listeServicesChemin;

        //Creer un nouveau lecteur xls
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        //$worksheet->getCell('B2')->getValue()
        for($i = 2 ; $i <= $worksheet->getHighestRow(); $i++){
            $tableauServices[$worksheet->getCell('A' . strval($i))->getValue() . ' - ' . $worksheet->getCell('B' . strval($i))->getValue()] = $worksheet->getCell('A' . strval($i))->getValue() . ' - ' . $worksheet->getCell('B' . strval($i))->getValue(); 
        }

        return $tableauServices; 
    }

    
}
