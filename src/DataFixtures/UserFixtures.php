<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\ScoringService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private ScoringService $scoringService;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ScoringService $scoringService
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->scoringService = $scoringService;
    }

    public function load(ObjectManager $manager): void
    {
        $educationLevels = [
            'Высшее образование',
            'Специальное образование',
            'Среднее образование'
        ];

        // Номера телефонов для разных операторов
        $operatorPhones = [
            'МегаФон' => '+7926',  // МегаФон
            'МТС' => '+7915',      // МТС
            'Билайн' => '+7903',   // Билайн
            'other' => '+7999'     // Иной оператор
        ];

        // Почтовые домены
        $emailDomains = [
            'gmail.com',
            'yandex.ru',
            'mail.ru',
            'other.com'  // иной домен
        ];

        $operators = array_keys($operatorPhones);

        // Создаем 5 пользователей-администраторов
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();

            // Выбираем случайный почтовый домен
            $domain = $emailDomains[array_rand($emailDomains)];
            $user->setEmail("user{$i}@{$domain}");

            $user->setRoles(['ROLE_ADMIN']);
            $user->setFirstname("Имя{$i}");
            $user->setLastname("Фамилия{$i}");

            // Выбираем случайного оператора
            $operator = $operators[array_rand($operators)];
            $phoneBase = $operatorPhones[$operator];
            $user->setPhone($phoneBase . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT));

            // Случайное образование
            $user->setEducation($educationLevels[array_rand($educationLevels)]);

            // Случайное согласие (true/false)
            $user->setConsent(rand(0, 1) === 1);

            $hashedPassword = $this->passwordHasher->hashPassword($user, "user{$i}pass");
            $user->setPassword($hashedPassword);

            // Рассчитываем баллы с помощью сервиса
            $score = $this->scoringService->calculateScore($user);
            $user->setScore($score);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
