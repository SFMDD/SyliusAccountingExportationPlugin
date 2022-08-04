<?php

declare(strict_types=1);

namespace FMDD\SyliusAccountingExportationPlugin\Form\Type;

use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', DateType::class, [
                'widget' => 'single_text',
                'label' => 'fmdd_accounting_exportation.form.from_date.label',
                'required' => true
            ])
            ->add('to', DateType::class, [
                'widget' => 'single_text',
                'label' => 'fmdd_accounting_exportation.form.to_date.label',
                'required' => true
            ])
            ->add('channels', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channels',
                'required' => true,
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                    new Assert\NotBlank([]),
                ],
            ])
        ;
    }
}
