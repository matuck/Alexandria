<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeaturedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reason')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('book')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'matuck\LibraryBundle\Entity\Featured'
        ));
    }

    public function getName()
    {
        return 'matuck_librarybundle_featuredtype';
    }
}
