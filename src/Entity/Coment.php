<?php

namespace App\Entity;

use App\Repository\ComentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComentRepository::class)]
class Coment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column]
    private ?bool $state = null;

    #[ORM\ManyToOne(inversedBy: 'coments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Author = null;

    #[ORM\ManyToOne(inversedBy: 'coments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(length: 255)]
    private ?string $clear = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->Author;
    }

    public function setAuthor(?User $Author): self
    {
        $this->Author = $Author;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getClear(): ?string
    {
        return $this->clear;
    }

    public function setClear(string $clear): self
    {
        $this->clear = $clear;

        return $this;
    }
}
