<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 512)]
    private ?string $accessToken;

    #[ORM\Column(type: 'datetime')]
    private $accessTokenExpiresAt;

    #[ORM\Column(type: 'string', length: 512)]
    private ?string $refreshToken;

    #[ORM\Column(type: 'datetime')]
    private $refreshTokenExpiresAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->accessTokenExpiresAt;
    }

    public function setAccessTokenExpiresAt(\DateTimeInterface $accessTokenExpiresAt): self
    {
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(\DateTimeInterface $refreshTokenExpiresAt): self
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;

        return $this;
    }
}
