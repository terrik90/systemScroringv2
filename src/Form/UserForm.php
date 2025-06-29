<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите email',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите имя',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите фамилию',
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
            ->add('consent')
            ->add('password', PasswordType::class, [
                'mapped' => !$options['is_edit'],
                'required' => !$options['is_edit'],
                'label' => 'Пароль',
                'constraints' => $options['is_edit'] ? [] : [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true,
                'data' => ['ROLE_ADMIN']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
