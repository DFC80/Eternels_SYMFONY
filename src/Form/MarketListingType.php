<?php

namespace App\Form;

use App\Entity\MarketListing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MarketListingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'annonce',
                'choices' => [
                    'Vente' => 'VENTE',
                    'Échange' => 'ECHANGE',
                    'Recherche / Achat' => 'RECHERCHE',
                ],
                'attr' => ['class' => 'form-select form-control-dark'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Ex : Tokyo Marui HK416 comme neuve',
                    'class' => 'form-control form-control-dark',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire'),
                    new Length(max: 200, maxMessage: 'Le titre ne peut pas dépasser 200 caractères'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez l\'article : état, marque, accessoires inclus, raison de la vente…',
                    'class' => 'form-control form-control-dark',
                    'rows' => 4,
                ],
                'constraints' => [
                    new NotBlank(message: 'La description est obligatoire'),
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Prix en euros (laisser vide si échange ou don)',
                    'class' => 'form-control form-control-dark',
                    'min' => '0',
                    'step' => '0.01',
                ],
                'constraints' => [],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => '— Choisir une catégorie —',
                'choices' => [
                    'Réplique' => 'REPLIQUE',
                    'Protection (masque, gilet…)' => 'PROTECTION',
                    'Tenue / Équipement' => 'TENUE',
                    'Accessoire (billes, batteries, chargeurs…)' => 'ACCESSOIRE',
                    'Autre' => 'AUTRE',
                ],
                'attr' => ['class' => 'form-select form-control-dark'],
            ])
            ->add('photoFiles', FileType::class, [
                'label' => 'Photos (optionnel)',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'class' => 'form-control form-control-dark',
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Seuls les formats JPG, PNG et WEBP sont acceptés.',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MarketListing::class,
        ]);
    }
}
