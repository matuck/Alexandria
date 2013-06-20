<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug', 'text', array('required' => false))
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'matuck\LibraryBundle\Entity\Tag',
            ))
            ->setOptional(array('createdAt', 'updatedAt'));
    }

    public function getName()
    {
        return 'matuck_librarybundle_tagtype';
    }
}
