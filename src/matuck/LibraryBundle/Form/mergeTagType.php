<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class mergeTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'entity', array(
                'class' => 'matuckLibraryBundle:Tag',
                'property' => 'name',
                'multiple' => true,
            ))
            ->add('to', 'entity', array(
                'class' => 'matuckLibraryBundle:Tag',
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
