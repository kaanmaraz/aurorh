<?php

namespace App\Exception;

use Exception;
use Throwable;

class EnvoiMailException extends Exception
{
    private $attributsManquants; 

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous); 
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} \n"; 
    }

    public function getAttributsManquants(): array
    {
        return $this->attributsManquants; 
    }

    public function getAttributsManquantsImplode(): string
    {
        return implode(',', $this->attributsManquants); 
    }

    public function setAttributsManquants( array $attributs): self
    {
        $this->attributsManquants = $attributs;
        $this->message .= "Veuillez renseigner les champs suivants : " . $this->getAttributsManquantsImplode();  
        return $this; 
    }


}