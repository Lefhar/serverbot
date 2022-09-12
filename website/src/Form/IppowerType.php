<?php

namespace App\Form;

use App\Entity\Ippower;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ShowHidePasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IppowerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class)
            ->add('password',ShowHidePasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ippower::class,
        ]);
    }
}
