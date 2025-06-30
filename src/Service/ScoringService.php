<?php

namespace App\Service;

use App\Entity\User;

class ScoringService
{
    private const MOBILE_OPERATOR_SCORES = [
        'МегаФон' => 10,
        'Билайн' => 5,
        'МТС' => 3,
        'other' => 1
    ];

    private const EMAIL_DOMAIN_SCORES = [
        'gmail.com' => 10,
        'yandex.ru' => 8,
        'mail.ru' => 6,
        'other' => 3
    ];

    private const EDUCATION_SCORES = [
        'Высшее образование' => 15,
        'Специальное образование' => 10,
        'Среднее образование' => 5,
        'other' => 0
    ];

    private const CONSENT_SCORES = [
        true => 4,
        false => 0
    ];

    public function calculateScore(User $user): int
    {
        $score = 0;

        $score += $this->getMobileOperatorScore($user->getPhone());
        $score += $this->getEmailDomainScore($user->getEmail());
        $score += $this->getEducationScore($user->getEducation());
        $score += $this->getConsentScore($user->isConsent());

        return $score;
    }

    public function getMobileOperatorScore(string $phoneNumber): int
    {
        $operator = $this->getMobileOperator($phoneNumber);
        return self::MOBILE_OPERATOR_SCORES[$operator] ?? self::MOBILE_OPERATOR_SCORES['other'];
    }

    public function getEmailDomainScore(string $email): int
    {
        $domain = $this->getEmailDomain($email);
        return self::EMAIL_DOMAIN_SCORES[$domain] ?? self::EMAIL_DOMAIN_SCORES['other'];
    }

    public function getEducationScore(string $education): int
    {
        return self::EDUCATION_SCORES[$education] ?? self::EDUCATION_SCORES['other'];
    }

    public function getConsentScore(bool $consent): int
    {
        return self::CONSENT_SCORES[$consent];
    }

    public function getMobileOperator(string $phoneNumber): string
    {
        // Предполагаем, что номер в формате +7XXXXXXXXXX
        $codes = [
            'МегаФон' => ['920', '921', '922', '923', '924', '925', '926', '927', '928', '929', '930', '931', '932', '933', '934', '935', '936', '937', '938', '939'],
            'Билайн' => ['902', '903', '904', '905', '906', '960', '961', '962', '963', '964', '965', '966', '967', '968', '969'],
            'МТС' => ['910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '980', '981', '982', '983', '984', '985', '986', '987', '988', '989']
        ];

        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        $code = substr($phoneNumber, 1, 3);

        foreach ($codes as $operator => $operatorCodes) {
            if (in_array($code, $operatorCodes)) {
                return $operator;
            }
        }

        return 'other';
    }

    public function getEmailDomain(string $email): string
    {
        return strtolower(substr(strrchr($email, "@"), 1));
    }
}
