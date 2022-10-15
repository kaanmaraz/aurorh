<?php

namespace App\Service;

use App\Entity\Candidat;

class Historique
{

    private $logMailChemin; 

    public function __construct($logMailChemin)
    {
        $this->logMailChemin = $logMailChemin; 
    }

    /**Pour un candidat donné renvoie son historique de mails */
    public function getHistoriqueMails(Candidat $candidat)
    {
        $lienEnvoyes = [];
        $cheminFichier = $this->logMailChemin.'/'.$candidat->getId().'.txt';
        $handle = @fopen($cheminFichier, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $lienEnvoyes[] = $buffer;
            }
            fclose($handle);
        }
        return $lienEnvoyes; 
    }

    /**Pour un candidat donné ajoute une date dans son historique de mails */
    public function addHistoriqueMails(Candidat $candidat)
    {
        //Insert la date d'envoi du mail dans un fichier de log
        $cheminFichier = $this->logMailChemin.'/'.$candidat->getId().'.txt';
        $date = date('d-m-Y H:i:s');
        $ligne = "$date\n";
        if (file_exists($cheminFichier)) {
            file_put_contents($cheminFichier, $ligne, FILE_APPEND | LOCK_EX);
        } else {
            // Écrit le résultat dans le fichier
            $fichier = fopen($cheminFichier, 'w+');
            file_put_contents($cheminFichier, $ligne, FILE_APPEND | LOCK_EX);
            fclose($fichier);
        } 
    }
}
