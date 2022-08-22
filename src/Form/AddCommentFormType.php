<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddCommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Rating input
            ->add("userRating", HiddenType::class, [
                "label" => false,
                "attr" => [
                    "class" => "user-rating-smile",
                    "value" => 0
                ],
            ])
            // Content input
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    "rows" =>8,
                    "placeholder"=>"Laissez votre commentaire ici ..."
                ],
                "purify_html"=>true,
                "constraints"=>[
                    new NotBlank([
                        "message"=>"Merci de renseigner un contenu"
                    ]),
                    new Length([
                        "min"=> 3,
                        "minMessage"=>"Le commentaire doit contenir au moins {{ limit }} caractères !",
                        "max"=> 2000,
                        "maxMessage"=>"Le commentaire doit contenir au maximum {{ limit }} caractères !"
                    ]),
                ],
            ])


            // Submit button
            ->add('save', SubmitType::class, [
                'label'=> "Commenter",
                "attr"=>[
                    "class"=>"btn btn-outline-danger w-100 fs-3"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
