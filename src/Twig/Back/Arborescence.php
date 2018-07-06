<?php
/**
 * Created by PhpStorm.
 * User: nadegehamann
 * Date: 11/09/2017
 * Time: 13:50
 */

namespace App\Twig\Back;


use App\Entity\Langue;
use App\Entity\Menu;
use App\Entity\MenuPage;
use App\Entity\Page;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class Arborescence extends \Twig_Extension
{
    public function __construct(RegistryInterface $doctrine, RequestStack $requestStack, Environment $twig)
    {
        $this->doctrine = $doctrine;
        $this->request = $requestStack->getCurrentRequest();
        $this->twig = $twig;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('menusBack', array($this, 'menusBack')),
            new \Twig_SimpleFunction('pagesOrphelines', array($this, 'pagesOrphelines')),
        );
    }

    public function menusBack()
    {
        //Menus
        $emMenu = $this->doctrine->getRepository(Menu::class);
        $menus = $emMenu->findAll();
        //Fin menus

        return $this->twig->render('back/menu/arborescence.html.twig', array('menus' => $menus));
    }

    public function pagesOrphelines()
    {
        $emLangue = $this->doctrine->getRepository(Langue::class);
        $langue = $emLangue->findOneBy(array('abreviation' => $this->request->getLocale()));

        $emPage = $this->doctrine->getRepository(Page::class);
        $emMenuPage = $this->doctrine->getRepository(MenuPage::class);

        $pages = $emPage->findBy(array('active' => 1, 'corbeille' => 0, 'langue' => $langue));

        $pagesOrphelines = [];
        foreach($pages as $page){
            $menuPage = $emMenuPage->findBy(array('page' => $page));

            if(!$menuPage){
                $pagesOrphelines[] = $page;
            }
        }

        return $this->twig->render('back/menu/pagesOrphelines.html.twig', array('pagesOrphelines' => $pagesOrphelines));
    }
}