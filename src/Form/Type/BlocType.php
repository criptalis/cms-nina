<?php
/**
 * Created by PhpStorm.
 * User: Chipolata
 * Date: 12/07/2018
 * Time: 13:56
 */

namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlocType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = scandir('../src/Blocs');
        $types = array_combine(array_values($types), array_values($types));
        unset($types["."]);
        unset($types[".."]);

        $builder
            ->add('type', ChoiceType::class, array(
                'required' => false,
                'empty_data' => null,
                'choices' => $types
            ))
            ->add('position', HiddenType::class, array('data'=>'0'))
            ->add('class', TextType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $bloc = $event->getData();
            $form = $event->getForm();

            if ($bloc){
                $type = $bloc->getType();
                $form->add('contenu', 'App\Blocs\\'.$type.'\\'.$type.'Type', array(
                    'label' => false
                ));
            }else{
                $form->add('contenu', CollectionType::class, array(
                    'allow_add' => true,
                    'label' => false
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Bloc',
        ));
    }

    public function getParent(){
        return FormType::class;
    }
}