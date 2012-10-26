<?php

namespace ServerGrove\KbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class ArticleTranslationType extends AbstractType
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['contentType'] = $form->get('contentType')->getData();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('max_length'=> 150, 'attr' => array('class' => 'input-xxlarge')));
        $builder->add('isActive', null, array('required' => false, 'label' => false));

        $builder->add('contentType', 'choice', array(
            'required' => true,
            'label'    => 'Type',
            'choices'  => array(
                'markdown' => 'Markdown',
                'wysiwyg'  => 'HTML'
            ),
            'expanded' => true,
            'multiple' => false,
            'attr'     => array('ng-model' => 'contentType')
        ));

        $builder->add('content', 'sg_editor', array(
            'required' => false,
            'attr'     => array(
                'class'    => 'input-xxlarge translation_content',
                'ng-model' => 'content'
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'ServerGrove\KbBundle\Document\Article', 'id_prefix' => ''));
    }

    public function getName()
    {
        return 'translation_'.$this->locale;
    }
}
