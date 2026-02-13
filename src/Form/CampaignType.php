<?php

namespace App\Form;

use App\Entity\Campaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['placeholder' => 'Enter campaign title']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'Describe your initiative...']
            ])
            ->add('financialGoal', NumberType::class, [
                'label' => 'Financial Goal ($)',
                'scale' => 2,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('totalInvestment', NumberType::class, [
                'label' => 'Total Investment ($)',
                'scale' => 2,
                'required' => false,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('roiPercentage', NumberType::class, [
                'label' => 'ROI (%)',
                'scale' => 2,
                'required' => false,
                'attr' => ['placeholder' => '0.00']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campaign::class,
        ]);
    }
}
