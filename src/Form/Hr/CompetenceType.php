<?php

namespace App\Form\Hr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('nom')
            ->add('membres')
            ->add('etudes')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Hr\Competence',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'hr_competence';
    }
}
