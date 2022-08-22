<?php

namespace App\Form;

use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditProfilFormType extends AbstractType
{
    // Function for limit year in input
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
            // Pseudonym input
            ->add('pseudonym', TextType::class, [
                'label' =>false,
                "purify_html"=>true,
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

            // Email input
            ->add('email', EmailType::class, [
                'label' => false,
                'constraints' => [
                    new Email([
                        'message' => "L'adresse email {{ value }} n'est pas une adresse valide"
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse email'
                    ]),
                ],
            ])

            // Birthdate input
            ->add('birthDate', BirthdayType::class,[
                'label' => false,
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

            // Biography input
            ->add('biography', CKEditorType::class, [
                'label' => false,
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

            // Save button
            ->add("save", SubmitType::class, [
                "label" => "Enregistrer les modifications",
                "attr" => [
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
