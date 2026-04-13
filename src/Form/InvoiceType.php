<?php

namespace App\Form;

use App\DTO\InvoiceData;
use App\Entity\Invoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientId', ChoiceType::class, [
                'label' => 'Client',
                'choices' => $options['client_choices'],
            ])
            ->add('projectId', ChoiceType::class, [
                'label' => 'Project',
                'choices' => $options['project_choices'],
                'required' => false,
                'placeholder' => 'No linked project',
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'html5' => true,
            ])
            ->add('issuedAt', DateType::class, [
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('dueAt', DateType::class, [
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Paid' => Invoice::STATUS_PAID,
                    'Unpaid' => Invoice::STATUS_UNPAID,
                ],
            ])
            ->add('description', TextareaType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoiceData::class,
            'client_choices' => [],
            'project_choices' => [],
        ]);
    }
}
