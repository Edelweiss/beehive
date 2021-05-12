<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;


class User implements UserInterface {
    private $id;
    private $username; // visual identifier that represents this user
    private $roles = [];
    private $password; // hashed password
    private $email;
    private $name;
    private $isActive;
    private $currentLogin;
    private $lastLogin;

    public function __construct() {
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): string {
        return (string) $this->username;
    }

    public function setUsername(string $username): self {
        $this->username = $username;
        return $this;
    }

    public function getRoles(): array {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // guarantee every user at least has ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): self {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string {
        return (string) $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;
        return $this;
    }

    public function getEmail(): string {
        return (string) $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getName(): string {
        return (string) $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getIsActive(): bool {
        return (bool) $this->isActive;
    }

    public function setIsActive(string $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCurrentLogin(): string {
        return (string) $this->currentLogin;
    }

    public function setCurrentLogin(string $currentLogin): self {
        $this->currentLogin = $currentLogin;
        return $this;
    }

    public function getLastLogin(): string {
        return (string) $this->lastLogin;
    }

    public function setLastLogin(string $lastLogin): self {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getSalt() {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials() {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
