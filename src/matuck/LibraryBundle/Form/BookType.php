<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isbn')
            ->add('title')
            ->add('summary')
            ->add('serieNbr')
            ->add('isPublic')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('rated')
            ->add('author', 'entity', array(
                   'class' => 'matuckLibraryBundle:Author',
                   'query_builder' => function($er) {
                        return $er->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC');
                    },
                   'property' => 'name',
                ))
            ->add('serie', 'entity', array(
                   'class' => 'matuckLibraryBundle:Serie',
                   'query_builder' => function($er) {
                        return $er->createQueryBuilder('s')
                        ->orderBy('s.name', 'ASC');
                    },
                   'property' => 'name',
                   'required' => FALSE
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'matuck\LibraryBundle\Entity\Book'
        ));
    }

    public function getName()
    {
        return 'matuck_librarybundle_booktype';
    }
}
