<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Flex\Configurator\ContainerConfigurator;
use Twig\Loader\LoaderInterface;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;


}
