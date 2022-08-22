<?php

namespace App\Form;

use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{

    //Format d'image accepter pour le champ pictureProfile
    private $allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
    ];

    private function getYears(): array
    {

        $oldestYear = date('Y') - 150;

        for($i = 0; $i <= 137; $i++){
            $years[] = $oldestYear+$i;
        }

        arsort($years);

        return $years;

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //TODO: Verifier les fautes d'orthographes ou voir à formuler autrement les champs

            // Champ Pseudonyme
            ->add('pseudonym', TextType::class, [
                'label' => 'Votre pseudonyme *',
                'constraints' => [
                    new NotBlank([
                        'message' => "Merci de renseigner un pseudonyme",
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => "Votre pseudonyme doit contenir au moins {{ limit }} caractères",
                        'maxMessage' => "Votre pseudonyme doit contenir au maximum {{ limit }} caractères",
                    ]),
                ],
            ])

            // Champ date d'anniversaire
            ->add('birthDate', BirthdayType::class,[
                'label' => 'Date de naissance *',
                'invalid_message' => 'Veuillez entrer une date de naissance valide.',
                'placeholder' => [
                    'year' => 'Années',
                    'month' => ' Mois',
                    'day' => 'Jour',
                ],
                'years' => $this->getYears(),
                'widget' => 'choice',
                'format' => 'dd-MM-yyyy',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner une date de naissance'
                    ]),
                    new LessThanOrEqual([
                        'value' => (new \DateTime('now'))->modify('-13 years'),
                        'message' => 'Vous devez avoir au moins 13 ans',
                    ])
                ]
            ])

            // Champ Email
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail *',
                'constraints' => [
                    new Email([
                        'message' => "L'adresse email {{ value }} n'est pas une adresse valide"
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse email'
                    ]),
                ],
            ])

            // Champ mot de passe et confirmation de mot de passe
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "Le mot de passe ne correspond pas à sa confirmation",
                'first_options' => [
                    'label' => 'Mot de passe *',
                ],
                'second_options' =>[
                    'label' => 'Confirmation mot de passe *',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Merci de renseigner un mot de passe"
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 4096,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Mot de passe trop grand',
                    ]),
                    new Regex([
                        'pattern' => "/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[ !\"\#\$%&\'\(\)*+,\-.\/:;<=>?@[\\^\]_`\{|\}~])^.{8,4096}$/u",
                        'message' => 'Votre mot de passe doit contenir obligatoirement une minuscule, une majuscule, un chiffre et un caractère spécial',
                    ]),
                ],
            ])


            //Champs Image de profil
            ->add('pictureProfil', FileType::class, [
                'label' => 'Sélectionnez une nouvelle photo',
                'attr' => [
                    'accept' => implode(', ', $this->allowedMimeTypes),
                ],
                'constraints' => [
                    new File([
                        'maxSize' => "10M",
                        'maxSizeMessage' => "Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum est de {{ limit }} {{ suffix }}",
                        'mimeTypes' => $this->allowedMimeTypes,
                        'mimeTypesMessage' => "Ce type de fichier n'est pas autorisé  ({{ type }}). es types autorisés sont {{ types }}",
                    ]),
                ],
            ])

            // Champs biographie
            ->add('biography', CKEditorType::class,[
                'label' => 'Biographie',
                'attr' => [
                    'class' => 'd-none',
                ],
                'purify_html' => true,
                'constraints' => [
                    new length([
                        'min' => 2,
                        'minMessage' => "Le contenue de contenir au moins {{ limit }} caractères",
                        'max' => 25000,
                        'maxMessage' => "Le contenue de contenir au maximum {{ limit }} caractères",
                    ]),
                ],
            ])

            //CGU
            ->add('terms', CheckboxType::class, [
                'label' => "veuillez accepter les ",
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation."
                    ])
                ]
            ])

            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => "Créer mon compte",
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
