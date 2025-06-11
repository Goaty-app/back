<?php

namespace App\Validator;

use App\Repository\UserRepository;
use LogicException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueUserEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUserEmail) {
            throw new LogicException('Unable to use UniqueUserEmail.');
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $existingUser = $this->userRepository->findOneBy(['email' => $value]);

        if ($existingUser) {
            $this->context->buildViolation($this->translator->trans('assert.unique_email'))
                ->setParameter('{{ value }}', $value)
                ->addViolation()
            ;
        }
    }
}
