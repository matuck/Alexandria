<?php

namespace matuck\LibraryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use matuck\LibraryBundle\Event\CommentFormSubscriber;

class CommentType extends AbstractType
{
    protected $securitycontext;
    protected $captcha;
    public function __construct(SecurityContext $securityContext, $captcha)
    {
        $this->securitycontext = $securityContext;
        $this->captcha = $captcha;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!$this->securitycontext->isGranted('IS_AUTHENTICATED_REMEMBERED') || !$this->securitycontext->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $builder->add('username', 'text', array('required' => false));
        }
        if($this->captcha)
        {
            $builder->add('captcha', 'captcha');
        }
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'matuck\LibraryBundle\Entity\Comment',
        ));
    }
    
    public function getParent()
    {
        return 'fos_comment_comment';
    }
 
    public function getName()
    {
        return "app_comment";
    }
}