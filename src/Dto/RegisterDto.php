<?php

namespace App\Dto;

use App\Validator\UniqueUserEmail;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegisterDto
{
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[UniqueUserEmail()]
    public string $email;

    #[Assert\NotBlank()]
    #[PasswordStrength(
        minScore: PasswordStrength::STRENGTH_STRONG,
    )]
    public string $password;
}
