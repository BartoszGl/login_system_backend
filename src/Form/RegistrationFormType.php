<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Message cannot be blank',
                    ]),
                    new Length([
                        // maksymalna długość 
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // W przypadku tej aplikacji będę korzystał z JWT, z defaultowego csrf_protection korzystałbym w momencie gdy 
            // tworzyłbym session-based authentication, ale to ma impakt na skalowalność aplikacji i późniejsze wykorzystanie
            // mechanizmu np przy tworzeniu aplikacji mobilnych. Poza tym rest api z definicji jest 'stateless': 
            // https://sherryhsu.medium.com/session-vs-token-based-authentication-11a6c5ac45e4
            // https://stackoverflow.com/questions/21285825/csrf-and-restful-api-symfony2-php
            // https://stackoverflow.com/questions/42246133/is-it-useful-to-use-csrf-token-protection-for-symfony-3-api-rest-and-angular-web?noredirect=1&lq=1
            // https://stackoverflow.com/questions/23220655/csrf-validation-needed-or-not-when-using-restful-api
            'csrf_protection' => false,
        ]);
    }
}
