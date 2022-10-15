<?php

namespace App\Service;

class LogsErreurs
{
    private $logErrWSChemin;
    private $logErrMailChemin;
	private $logErrChemin; 

    public function __construct($logErrWSChemin, $logErrMailChemin, $logErrChemin)
    {
        $this->logErrWSChemin = $logErrWSChemin;
        $this->logErrMailChemin = $logErrMailChemin;
		$this->logErrChemin = $logErrChemin; 
    }

    /**ajoute des logs pour des exceptions levées par le WebService */
    public function ajouterLogWS($erreur)
    {
        $log = '====================== Exception levée : '.date('Y-m-d H:i:s')." ====================== \n";
        $log .= "$erreur\n";
        if (file_exists($this->logErrWSChemin)) {
            file_put_contents($this->logErrWSChemin, $log, FILE_APPEND | LOCK_EX);
        } else {
            // Écrit le résultat dans le fichier
            $fichier = fopen($this->logErrWSChemin, 'w+');
            file_put_contents($this->logErrWSChemin, $log, FILE_APPEND | LOCK_EX);
            fclose($fichier);
        }
    }

    /**ajoute des logs pour des exceptions levées par le SwiftMailer */
    public function ajouterLogMail($erreur)
    {
        $log = '====================== Exception levée : '.date('Y-m-d H:i:s')." ====================== \n";
        $log .= "$erreur\n";
        if (file_exists($this->logErrMailChemin)) {
            file_put_contents($this->logErrMailChemin, $log, FILE_APPEND | LOCK_EX);
        } else {
            // Écrit le résultat dans le fichier
            $fichier = fopen($this->logErrMailChemin, 'w+');
            file_put_contents($this->logErrMailChemin, $log, FILE_APPEND | LOCK_EX);
            fclose($fichier);
        }
    }
    /**ajoute des logs pour des exceptions levées par des fonctions */
	public function ajouterLogFonction($erreur)
    {
        $log = '====================== Exception levée : '.date('Y-m-d H:i:s')." ====================== \n";
        $log .= "$erreur\n";
        if (file_exists($this->logErrChemin)) {
            file_put_contents($this->logErrChemin, $log, FILE_APPEND | LOCK_EX);
        } else {
            // Écrit le résultat dans le fichier
            $fichier = fopen($this->logErrChemin, 'w+');
            file_put_contents($this->logErrChemin, $log, FILE_APPEND | LOCK_EX);
            fclose($fichier);
        }
    }
}
