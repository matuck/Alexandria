<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FlagsType extends AbstractType
{
    private $types;
    public function __construct(array $types)
    {
        foreach($types as $type)
        {
            $this->types[$type] = $type;
        }
//        parent::__construct();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array('choices' => $this->types))
            ->add('title')
            ->add('complete')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('book')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'matuck\LibraryBundle\Entity\Flags'
        ));
    }

    public function getName()
    {
        return 'matuck_librarybundle_flagstype';
    }
}
