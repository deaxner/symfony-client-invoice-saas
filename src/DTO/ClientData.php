<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ClientData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $company = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\Length(max: 50)]
    public ?string $phone = null;

    #[Assert\Length(max: 5000)]
    public ?string $address = null;
}
