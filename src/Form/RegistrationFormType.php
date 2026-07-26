<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prenom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre prenom'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prenom']),
                    new Length(['min' => 2, 'minMessage' => 'Le prenom doit contenir au moins 2 caracteres']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom']),
                    new Length(['min' => 2, 'minMessage' => 'Le nom doit contenir au moins 2 caracteres']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'votre@email.fr'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email']),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telephone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '06 XX XX XX XX'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Mot de passe (8 car. min)'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Confirmez le mot de passe'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caracteres',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
