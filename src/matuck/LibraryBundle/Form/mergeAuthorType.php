<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class mergeAuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'entity', array(
                'class' => 'matuckLibraryBundle:Author',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                                  ->orderBy('a.name', 'ASC');
                                },
                'property' => 'name',
                'multiple' => true,
            ))
            ->add('to', 'entity', array(
                'class' => 'matuckLibraryBundle:Author',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                                  ->orderBy('a.name', 'ASC');
                                },
                'property' => 'name',
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        
    }

    public function getName()
    {
        return 'matuck_librarybundle_mergetagtype';
    }
}
