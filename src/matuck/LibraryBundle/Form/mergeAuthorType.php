<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class mergeAuthorType extends AbstractType
{
    public function __construct(\matuck\LibraryBundle\Entity\Author $author)
    {
        $this->author = $author;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('to', 'entity', array(
                'class' => 'matuckLibraryBundle:Author',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')->where('a.id != :authorid')
                                  ->orderBy('a.name', 'ASC')->setParameter('authorid', $this->author->getId());
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
