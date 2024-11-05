<?php

namespace App\Form;

use App\Entity\Wallet;
use App\Entity\WalletOperation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WalletOperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('payerOperator', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Mode de paiement',
                'choices' => [
                    'COMPTE VIRTUEL' => [
                        'MAKUTA' => "MAKUTA",
                        'ECOBANKPAY' => "DRC_ECOBANKPAY",
                        'RAKKACASH' => "DRC_RAKKACASH",
                        'ILLICOCASH' => "DRC_ILLICOCASH",
                    ],
                    'MONNAIE-MOBILE' => [
                        'MPESA' => "DRC_MPESA",
                        'ORANGE MONEY' => "DRC_ORANGE_MONEY",
                        'AIRTEL MONEY' => "DRC_AIRTEL_MONEY",
                        'AFRIMONEY' => "DRC_AFRIMONEY",
                    ],
                    'CARTE ELECTRONIQUE' => [
                        'CARTE BANCAIRE' => "WEB_CARD",
                        'VISA TAP2PHONE' => "VISA",
                    ],
                    'CASH' => [
                        'CASH' => "CASH",
                    ],
                    'COMPTE BANCAIRE' => [
                        'FIRSTBANK' => "DRC_FIRSTBANK",
                    ],
                ],
                'required' => true,
                'placeholder' => '- Mode de paiement -',
            ])
            ->add('isDefaultAmount', HiddenType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('payerCurrency', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Devise',
                'choices' => [
                    'USD' => "USD",
                    'CDF' => "CDF",
                    'PTS' => "PTS",
                ],
            ])
            ->add('payerAccountNumber', TextType::class, [
                'mapped' => false,
                'label' => 'No. Téléphone',
                'required' => true,
                'attr' => [
                    'placeholder' => 'No. Téléphone',
                    'maxlength' => 25,
                ],
            ])
            ->add('countryCode', HiddenType::class,
                [
                    'mapped' => false,
                ])
//            ->add('fullname', TextType::class, [
//                'mapped' => false,
//                'label' => 'Nom Complet',
//                'required' => true,
//                'attr' => [
//                    'placeholder' => 'Prénoms et Noms',
//                    'maxlength' => 50,
//                ],
//            ])
//            ->add('address', TextType::class, [
//                'mapped' => false,
//                'label' => 'Adresse',
//                'required' => true,
//                'attr' => [
//                    'placeholder' => 'Ex: 1, Av. Wag, C. Gombe, Kinshasa, RDC',
//                    'maxlength' => 255,
//                ],
//            ])
            ->add('amount', TextType::class, [
                'label' => 'Montant à payer',
                'required' => true,
                'attr' => [
                    'placeholder' => '0.00',
                    'maxlength' => 9,
                    'min' => 1,
                    'max' => 999999999,
                ],
            ])
            ->add('submit', SubmitType::class, [

                'label' => 'Confirmer maintenant',
                'attr' => [
                    'class' => 'btn btn-falcon-danger btn-md rounded-sm-capsule rounded-capsule p-2 fs-1 pl-4 pr-4',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WalletOperation::class,
        ]);
    }
}
