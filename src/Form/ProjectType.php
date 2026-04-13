<?php

namespace App\Form;

use App\DTO\ProjectData;
use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientId', ChoiceType::class, [
                'label' => 'Client',
                'choices' => $options['client_choices'],
            ])
            ->add('name')
            ->add('code')
            ->add('billingModel', ChoiceType::class, [
                'choices' => [
                    'Hourly' => Project::BILLING_HOURLY,
                    'SLA' => Project::BILLING_SLA,
                    'Fixed retainer' => Project::BILLING_FIXED_RETAINER,
                ],
            ])
            ->add('hourlyRate', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'html5' => true,
            ])
            ->add('internalCostRateDefault', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'html5' => true,
            ])
            ->add('slaMonthlyFee', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'html5' => true,
            ])
            ->add('monthlyHoursIncluded', IntegerType::class, [
                'required' => false,
            ])
            ->add('fixedMonthlyRetainer', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'html5' => true,
            ])
            ->add('activeFrom', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('activeUntil', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectData::class,
            'client_choices' => [],
        ]);
    }
}
