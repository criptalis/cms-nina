<?php
/**
 * Created by PhpStorm.
 * User: nadegehamann
 * Date: 21/08/2017
 * Time: 16:27
 */

namespace App\Service;


use App\Entity\Langue;
use App\Entity\SEOPage;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class Page
{
    public function __construct(RegistryInterface $doctrine, RequestStack $request, RouterInterface $router)
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->router = $router;
    }

    public function getPageActive()
    {
        $currentRequest = $this->request->getCurrentRequest();
        if($currentRequest){
            $routes = ['accueil', 'accueilLocale', 'voirPage', 'voirPageLocale'];
            $route = $currentRequest->attributes->get('_route');

            $locale = $currentRequest->getLocale();
            $url = $currentRequest->attributes->get('url');

            if($url){
                //$_locale
                $repoLangue = $this->doctrine->getRepository(Langue::class);
                $langue = $repoLangue->findOneBy(array('abreviation' => $locale));

                if(!$langue){//Si l'utilisateur essaye de naviguer sur une langue qui n'existe page
                    $langue = $repoLangue->findOneBy(array('defaut' => true));
                    return new RedirectResponse($this->router->generate('accueilLocale', array('_locale' => $langue->getAbreviation())));
                }

                $repository = $this->doctrine->getRepository(SEOPage::class);
                $seos = $repository->findByUrl($url);

                if(!$seos){
                    if(!in_array($route, $routes)){
                        return null;
                    }
                    throw new NotFoundHttpException('Cette page n\'existe pas ou a été supprimée');
                }

                foreach($seos as $seo){
                    if($langue == $seo->getPage()->getLangue()){
                        $page = $seo->getPage();
                    }
                }

                if(!isset($page)){
                    throw new NotFoundHttpException('Cette page n\'existe pas ou a été supprimée');
                }

                if($langue->getPageAccueil() == $page){
                    if($langue->getDefaut()){
                        return new RedirectResponse($this->router->generate('accueil'));
                    }else{
                        return new RedirectResponse($this->router->generate('accueilLocale', array('_locale' => $langue->getAbreviation())));
                    }
                }

                $timestamp = new \DateTime();
                $date = $timestamp->format('Y-m-d H:i:s');

                if(!$page->getDatePublication() < $date && $page->getDateDepublication() > $date && $page->getCorbeille()=="0" && $page->getActive()=="1") {
                    throw new NotFoundHttpException('Cette page n\'est plus accessible');
                }
            }else{
                if(!in_array($route, $routes)){
                    return null;
                }

                $repoLangue = $this->doctrine->getRepository(Langue::class);

                if(isset($locale)){
                    $langue = $repoLangue->findOneBy(array('abreviation' => $locale));

                    if(!$langue){//Si l'utilisateur essaye de naviguer sur une langue qui n'existe page
                        $langue = $repoLangue->findOneBy(array('defaut' => true));
                        return new RedirectResponse($this->router->generate('accueilLocale', array('_locale' => $langue->getAbreviation())));
                    }

                    $currentRequest->getSession()->set('_locale', $locale);
                }else{//On utilise la langue par défaut si aucune locale n'est précisée
                    $langue = $repoLangue->findOneBy(array('defaut' => true));

                    if($locale !== $langue->getAbreviation()){//Si la locale n'est pas la langue par défaut, on la modifie
                        $currentRequest->getSession()->set('_locale', $langue->getAbreviation());
                    }
                }

                $page = $langue->getPageAccueil();

                if(!$page){
                    throw new NotFoundHttpException('Aucune page d\'accueil configurée pour cette langue');
                }
            }

            return $page;
        }

        return null;
    }
}