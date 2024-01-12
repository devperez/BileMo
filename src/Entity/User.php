<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use JMS\Serializer\Annotation\Groups;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *  "self",
 *  href = @Hateoas\Route(
 *      "detailUser",
 *      parameters = { "id" = "expr(object.getId())"}
 *  ),
 * exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 *
 *
 * @Hateoas\Relation(
 *  "delete",
 *  href = @Hateoas\Route(
 *      "deleteUser",
 *      parameters = {"id" = "expr(object.getId())"}
 *  ),
 *  exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le nom de famille est obligatoire")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire")]
    private ?string $userName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCustomer(): ?customer
    {
        return $this->customer;
    }

    public function setCustomer(?customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
