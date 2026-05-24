<?php

namespace App\Entity;

use App\Repository\MensajeContactoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MensajeContactoRepository::class)]
class MensajeContacto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $nombre;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $asunto;

    #[ORM\Column(type: Types::TEXT)]
    private string $mensaje;

    #[ORM\Column]
    private \DateTimeImmutable $creadoEn;

    #[ORM\Column]
    private bool $leido = false;

    public function __construct()
    {
        $this->creadoEn = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getNombre(): string { return $this->nombre; }
    public function setNombre(string $nombre): static { $this->nombre = $nombre; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getAsunto(): string { return $this->asunto; }
    public function setAsunto(string $asunto): static { $this->asunto = $asunto; return $this; }

    public function getMensaje(): string { return $this->mensaje; }
    public function setMensaje(string $mensaje): static { $this->mensaje = $mensaje; return $this; }

    public function getCreadoEn(): \DateTimeImmutable { return $this->creadoEn; }

    public function isLeido(): bool { return $this->leido; }
    public function setLeido(bool $leido): static { $this->leido = $leido; return $this; }
}
