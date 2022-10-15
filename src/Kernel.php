<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    public function getCacheDir(): string
    {
        // for docker performance
        if ($this->getEnvironment() === 'test' || $this->getEnvironment() === 'dev') {
            return '/tmp/'.$this->environment;
        } else {
            return $this->getProjectDir().'/var/cache/'.$this->environment;
        }

    }
}
