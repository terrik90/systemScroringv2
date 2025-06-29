<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ScoringService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:calculate-scoring',
    description: 'Рассчитывает скоринг для пользователей',
)]
class CalculateScoringCommand extends Command
{
    public function __construct(
        private readonly ScoringService $scoringService,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'user-id',
                InputArgument::OPTIONAL,
                'ID пользователя для расчета скоринга (если не указан, будет произведен расчет для всех пользователей)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('user-id');

        if ($userId) {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                $io->error(sprintf('Пользователь с ID "%s" не найден', $userId));
                return Command::FAILURE;
            }

            $oldScore = $user->getScore();
            $newScore = $this->scoringService->calculateScore($user);
            $user->setScore($newScore);
            $this->entityManager->flush();

            $io->section(sprintf(
                'Скоринг для пользователя %s %s (ID: %d)',
                $user->getFirstname(),
                $user->getLastname(),
                $user->getId()
            ));

            $this->outputScoringDetails($io, $user, $oldScore);

            return Command::SUCCESS;
        }

        $users = $this->userRepository->findAll();
        if (empty($users)) {
            $io->warning('В базе данных нет пользователей');
            return Command::SUCCESS;
        }

        $io->title('Расчет скоринга для всех пользователей');

        foreach ($users as $user) {
            $oldScore = $user->getScore();
            $newScore = $this->scoringService->calculateScore($user);
            $user->setScore($newScore);

            $io->section(sprintf(
                'Пользователь %s %s (ID: %d)',
                $user->getFirstname(),
                $user->getLastname(),
                $user->getId()
            ));

            $this->outputScoringDetails($io, $user, $oldScore);
        }

        $this->entityManager->flush();
        $io->success(sprintf('Скоринг успешно обновлен для %d пользователей', count($users)));

        return Command::SUCCESS;
    }

    private function outputScoringDetails(SymfonyStyle $io, User $user, ?int $oldScore): void
    {
        $phoneOperator = $this->scoringService->getMobileOperator($user->getPhone());
        $emailDomain = $this->scoringService->getEmailDomain($user->getEmail());

        $operatorScore = match ($phoneOperator) {
            'МегаФон' => 10,
            'Билайн' => 5,
            'МТС' => 3,
            default => 1,
        };

        $emailScore = match ($emailDomain) {
            'gmail.com' => 10,
            'yandex.ru' => 8,
            'mail.ru' => 6,
            default => 3,
        };

        $educationScore = match ($user->getEducation()) {
            'Высшее образование' => 15,
            'Специальное образование' => 10,
            'Среднее образование' => 5,
            default => 0,
        };

        $consentScore = $user->isConsent() ? 4 : 0;

        $io->table(
            ['Параметр', 'Значение', 'Баллы'],
            [
                ['Сотовый оператор', $phoneOperator, $operatorScore],
                ['Домен эл. почты', $emailDomain, $emailScore],
                ['Образование', $user->getEducation(), $educationScore],
                ['Согласие на обработку', $user->isConsent() ? 'Да' : 'Нет', $consentScore],
                ['Итого', '', $user->getScore()],
            ]
        );

        $io->info(sprintf(
            'Скоринг обновлен: %d -> %d',
            $oldScore ?? 0,
            $user->getScore()
        ));
    }
}
