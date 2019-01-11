<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Langue;
use App\Entity\MenuPage;
use App\Entity\SEO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends Controller
{
    /**
     * @Route("/{_locale}/{url}", name="voirPage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function voirAction(Request $request, $url){
        //$_locale
        $repoLangue = $this->getDoctrine()->getRepository(Langue::class);
        $langue = $repoLangue->findOneBy(array('abreviation' => $request->getLocale()));

        if(!$langue){//Si l'utilisateur essaye de naviguer sur une langue qui n'existe page
            $langue = $repoLangue->findOneBy(array('defaut' => true));
            return $this->redirectToRoute('accueilLocale', array('_locale' => $langue->getAbreviation()));
        }

        $repository = $this->getDoctrine()->getRepository(SEO::class);
        $seos = $repository->findByUrl($url);

        if(!$seos){
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
            return $this->redirectToRoute('accueil');
        }

        $timestamp = new \DateTime();
        $date = $timestamp->format('Y-m-d H:i:s');

        if(!$page->getDatePublication() < $date && $page->getDateDepublication() > $date && $page->getCorbeille()=="0" && $page->getActive()=="1") {
            throw new NotFoundHttpException('Cette page n\'est plus accessible');
        }

        $commentaires = $page->getCommentaires();

        $commentaire = new Commentaire();

        $utilisateur = $this->getUser();
        if ($utilisateur){
            $commentaire->setAuteur($utilisateur->getUsername());
            $commentaire->setEmail($utilisateur->getEmail());
        }else{
            $commentaire->setAuteur('Anonyme');
        }

        /* Menus */
        $repoMenuPages = $this->getDoctrine()->getRepository(MenuPage::class);
        $menusPages = $repoMenuPages->findBy(array('page' => $page));
        $menus = [];
        foreach($menusPages as $menuPage){
            $menu = $menuPage->getMenu();
            if($menu){
                $menus[] = $menu->getId();
            }
        }
        $menus = array_unique($menus);
        /* Fin menus */

        /* Commentaires */
            $commentaire->setPage($page);

            $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $commentaire);

            $formBuilder
                ->add('auteur', TextType::class, array('label' => 'Votre nom :'))
                ->add('email', EmailType::class, array('label' => 'Votre adresse e-mail :'))
                ->add('site', TextType::class, array('label' => 'Votre site web :', 'required' => false))
                ->add('contenu', TextareaType::class, array('label'=>'Votre commentaire :'))
                ->add('envoi', SubmitType::class, array('attr'=>array('class'=>'envoiCom')));

            $form=$formBuilder->getForm();

            if($request->isMethod('POST')){
                $form->handleRequest($request);
                if($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($commentaire);
                    $em->flush();

                    $request->getSession()->getFlashBag()->add('comOK', 'Votre commentaire a été enregistré et sera mis en ligne une fois validé');

                    return $this->redirectToRoute('voirPage', array('url' => $seo->getUrl()));
                }
            }
        /* Fin commentaires */

        return $this->render('front/page.html.twig', array('page'=>$page, 'form'=>$form->createView(), 'commentaires'=>$commentaires, 'menusDeLaPage'=>$menus));
    }
}
