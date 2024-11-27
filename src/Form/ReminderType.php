<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Reminder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('createdDate', null, [
                'widget' => 'single_text'
            ])
            ->add('limitDate', null, [
                'widget' => 'single_text'
            ])
            ->add('isDone')
            ->add('idCategory', EntityType::class, [
                'class' => Category::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reminder::class,
        ]);
    }
}
