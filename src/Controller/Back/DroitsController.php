<?php
/**
 * Created by PhpStorm.
 * User: nadege
 * Date: 2019-08-21
 * Time: 09:36
 */

namespace App\Controller\Back;

use App\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DroitsController
 * @package App\Controller\Back
 * @Route("/admin")
 */
class DroitsController extends Controller
{
    /**
     * @Route("/droits", name="droits")
     * @param Request $request
     * @return bool|Response
     */
    public function listeDroits(Request $request){
        //Rôles
        $repoRoles = $this->getDoctrine()->getRepository(Role::class);
        $roles = $repoRoles->findAll();

        //Droits
        $droits = Yaml::parsefile('../config/droits.yaml');

        //Création de rôle
        $role = new Role;
        $formCreationRole = $this->createFormBuilder($role)
            ->add('nom', TextType::class, ['label' => false])
            ->add('Enregistrer', SubmitType::class);

        $formCreationRole->get('nom')
            ->addModelTransformer(new CallbackTransformer(
                function ($role) {
                    return substr($role, 5, strlen($role));
                },
                function ($role) {
                    return 'ROLE_'.$role;
                }
            ))
        ;

        $formCreationRole = $formCreationRole->getForm();

        $formCreationRole->handleRequest($request);

        //Enregistrement nouveau rôle
        if ($formCreationRole->isSubmitted()) {
            if($formCreationRole->isValid()){
                $em = $this->getDoctrine()->getManager();

                $role = $formCreationRole->getData();

                $em->persist($role);
                $em->flush();

                $this->addFlash('enregistrement', 'Le rôle "'.$role->getNom().'" a été créé');
            }else{
                $erreurs = $formCreationRole->getErrors();

                foreach($erreurs as $erreur){
                    $this->addFlash('erreur', $erreur->getMessage());
                }
            }

            return $this->redirectToRoute('droits');
        }

        //Enregistrement droits
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();

            foreach($roles as $role){
                foreach($droits as $categorie){
                    foreach($categorie as $droit => $label){
                        $droitsRole = $role->getDroits();
                        if(key_exists($droit, $_POST)){
                            $droitsRole[$droit] = in_array($role->getNom(), $_POST[$droit]);
                        }else{//Droit accordé à aucun rôle
                            $droitsRole[$droit] = false;
                        }
                        $role->setDroits($droitsRole);
                    }
                }
                $em->persist($role);
            }

            $em->flush();

            $this->addFlash('enregistrement', 'Les droits ont été enregistrés');
        }

        $entityConfig = ['name' => 'Droit'];

        return $this->render('back/droits.html.twig', ['roles' => $roles, 'droits' => $droits, 'formCreationRole' => $formCreationRole->createView(), '_entity_config' => $entityConfig]);
    }
}