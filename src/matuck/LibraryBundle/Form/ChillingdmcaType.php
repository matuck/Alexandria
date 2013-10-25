<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChillingdmcaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookTitle')
            ->add('bookAuthor')
            ->add('dmcaName')
            ->add('dmcaEmail', 'email')
            ->add('ipAddress')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'matuck\LibraryBundle\Entity\Chillingdmca'
        ));
    }

    public function getName()
    {
        return 'matuck_librarybundle_chillingdmcatype';
    }
}
