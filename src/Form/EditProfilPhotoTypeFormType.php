<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EditProfilPhotoTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // File input
            ->add('pictureProfil', FileType::class, [
                'label'=>false,
                'constraints'=>[
                    new File([
                        // File size
                        'maxSize' => "10M",
                        'maxSizeMessage' => "Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum est de {{ limit }} {{ suffix }}",

                        // File MIME type
                        'mimeTypes'=>[
                            'jpeg' => 'image/jpeg',
                            'jpg' => 'image/jpg',
                            'png' => 'image/png',
                        ],
                        'mimeTypesMessage' => "Ce type de fichier n'est pas autorisé ({{ type }}). Les types autorisés sont {{ types }}",
                    ]),
                ],
            ])

            // Submit button
            ->add('save', SubmitType::class, [
                'label' => "Enregistrer votre photo",
                'attr' => [
                    'class' => 'btn btn-outline-danger col-12',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
