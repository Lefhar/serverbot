<?php

namespace App\Form;

use App\Entity\Server;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('ippower', ChoiceType::class, array(
                'choices' => [
                    'prise 1' => '1',
                    'prise 2' => '2',
                    'prise 3' => '3',
                    'prise 4' => '4',
                ]))
            ->add('etat', ChoiceType::class, array(
                'choices' => [
                    'Inactif' => '0',
                    'Actif' => '1'
                ],'required' => true))
            ->add('ipv4')
            ->add('location', ChoiceType::class, array(
                'choices' => [
                    'local' => 'local',
                    'distant' => 'distant'
                ],'required' => true))
            ->add('localscript')
            ->add('lieninfo')
            ->add('machine',null,['required'=>true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Server::class,
        ]);
    }
}
