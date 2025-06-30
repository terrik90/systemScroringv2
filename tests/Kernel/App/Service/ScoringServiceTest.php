<?php

namespace App\Tests\Kernel\App\Service;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\ScoringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class ScoringServiceTest extends KernelTestCase
{
    use ResetDatabase;

    private ScoringService $scoringService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->scoringService = static::getContainer()->get(ScoringService::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Очищаем EntityManager после каждого теста
        $this->entityManager->clear();

        // Закрываем EntityManager
        $this->entityManager->close();
    }

    #[DataProvider('providePhoneOperatorCases')]
    public function testMobileOperatorDetection(string $phoneNumber, string $expectedOperator): void
    {
        $this->assertEquals(
            $expectedOperator,
            $this->scoringService->getMobileOperator($phoneNumber)
        );
    }

    public static function providePhoneOperatorCases(): array
    {
        return [
            'МегаФон номер' => ['+79201234567', 'МегаФон'],
            'Билайн номер' => ['+79051234567', 'Билайн'],
            'МТС номер' => ['+79101234567', 'МТС'],
            'Неизвестный оператор' => ['+79001234567', 'other'],
        ];
    }

    #[DataProvider('provideEmailDomainCases')]
    public function testEmailDomainDetection(string $email, string $expectedDomain): void
    {
        $this->assertEquals(
            $expectedDomain,
            $this->scoringService->getEmailDomain($email)
        );
    }

    public static function provideEmailDomainCases(): array
    {
        return [
            'Gmail домен' => ['test@gmail.com', 'gmail.com'],
            'Yandex домен' => ['test@yandex.ru', 'yandex.ru'],
            'Mail.ru домен' => ['test@mail.ru', 'mail.ru'],
            'Другой домен' => ['test@example.com', 'example.com'],
        ];
    }

    #[DataProvider('provideScoringCases')]
    public function testScoreCalculation(
        string $phone,
        string $email,
        string $education,
        bool $consent,
        int $expectedScore
    ): void {
        // Создаем пользователя без сохранения в базу
        $user = new User();
        $user->setPhone($phone);
        $user->setEmail($email);
        $user->setEducation($education);
        $user->setConsent($consent);

        $this->assertEquals(
            $expectedScore,
            $this->scoringService->calculateScore($user)
        );
    }

    public static function provideScoringCases(): array
    {
        return [
            'Максимальный скор' => [
                '+79201234567', // МегаФон (10 баллов)
                'test@gmail.com', // Gmail (10 баллов)
                'Высшее образование', // (15 баллов)
                true, // Согласие (4 балла)
                39 // Общий счет
            ],
            'Средний скор' => [
                '+79051234567', // Билайн (5 баллов)
                'test@mail.ru', // Mail.ru (6 баллов)
                'Специальное образование', // (10 баллов)
                true, // Согласие (4 балла)
                25 // Общий счет
            ],
            'Минимальный скор' => [
                '+79001234567', // Другой оператор (1 балл)
                'test@example.com', // Другой домен (3 балла)
                'other', // Другое образование (0 баллов)
                false, // Нет согласия (0 баллов)
                4 // Общий счет
            ],
            'МТС + Яндекс + Среднее' => [
                '+79101234567', // МТС (3 балла)
                'test@yandex.ru', // Яндекс (8 баллов)
                'Среднее образование', // (5 баллов)
                true, // Согласие (4 балла)
                20 // Общий счет
            ],
        ];
    }

    public function testIndividualScoreComponents(): void
    {
        // Создаем пользователя без сохранения в базу
        $user = new User();
        $user->setPhone('+79201234567'); // МегаФон (10 баллов)
        $user->setEmail('test@gmail.com'); // Gmail (10 баллов)
        $user->setEducation('Высшее образование'); // (15 баллов)
        $user->setConsent(true); // Согласие (4 балла)

        $score = $this->scoringService->calculateScore($user);

        // Проверяем каждый компонент отдельно
        $this->assertEquals(10, $this->scoringService->getMobileOperatorScore($user->getPhone()));
        $this->assertEquals(10, $this->scoringService->getEmailDomainScore($user->getEmail()));
        $this->assertEquals(15, $this->scoringService->getEducationScore($user->getEducation()));
        $this->assertEquals(4, $this->scoringService->getConsentScore($user->isConsent()));

        // Проверяем общий счет
        $this->assertEquals(39, $score);
    }
}
