<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите ваше имя',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите вашу фамилию',
                    ]),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Номер телефона',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите номер телефона',
                    ]),
                    new Regex([
                        'pattern' => '/^\+7[0-9]{10}$/',
                        'message' => 'Пожалуйста, введите корректный российский номер телефона в формате +7XXXXXXXXXX',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email',
                    ]),
                    new Email([
                        'message' => 'Пожалуйста, введите корректный email адрес',
                    ]),
                ],
            ])
            ->add('education', ChoiceType::class, [
                'label' => 'Образование',
                'choices' => [
                    'Среднее образование' => 'Среднее образование',
                    'Специальное образование' => 'Специальное образование',
                    'Высшее образование' => 'Высшее образование',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, выберите уровень образования',
                    ]),
                ],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'Я даю согласие на обработку моих персональных данных',
                'required' => false,
                'mapped' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Пароль',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ваш пароль должен содержать как минимум {{ limit }} символов',
                        'max' => 4096,
                    ]),
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
