<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Votre prénom', 'class' => 'form-control form-control-dark'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre prénom'),
                    new Length(min: 2, minMessage: 'Le prénom doit contenir au moins 2 caractères'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Votre nom', 'class' => 'form-control form-control-dark'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre nom'),
                    new Length(min: 2, minMessage: 'Le nom doit contenir au moins 2 caractères'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'votre@email.fr', 'class' => 'form-control form-control-dark'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre email'),
                    new Email(message: 'Adresse email invalide'),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => '06 XX XX XX XX', 'class' => 'form-control form-control-dark'],
            ])
            ->add('address', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Votre adresse', 'class' => 'form-control form-control-dark'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
