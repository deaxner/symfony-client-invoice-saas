<?php

namespace App\Service;

use App\DTO\RegistrationData;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function register(RegistrationData $data): User
    {
        if ($this->userRepository->findOneBy(['email' => mb_strtolower((string) $data->email)])) {
            throw new ConflictHttpException('An account with this email already exists.');
        }

        $user = (new User())
            ->setEmail((string) $data->email)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, (string) $data->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
