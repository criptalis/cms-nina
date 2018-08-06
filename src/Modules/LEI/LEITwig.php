<?php
/**
 * Created by PhpStorm.
 * User: Chipolata
 * Date: 17/07/2018
 * Time: 13:35
 */

namespace App\Modules\LEI;


use App\Entity\Module;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Twig\Environment;

class LEITwig extends \Twig_Extension
{
    public function __construct(RegistryInterface $doctrine, Environment $twig)
    {
        $this->doctrine = $doctrine;
        $this->twig = $twig;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('listeLEI', array($this, 'listeLEI')),
        );
    }

    public function listeLEI($flux, $limite = null)
    {
        $xml = simplexml_load_file($flux)->Resultat->children();

        return $this->twig->render('Modules/LEI/liste.html.twig', array('xml' => $xml, 'limite' => $limite));
    }
}