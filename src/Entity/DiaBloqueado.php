<?php
// src/Entity/DiaBloqueado.php

namespace App\Entity;

use App\Repository\DiaBloqueadoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiaBloqueadoRepository::class)]
class DiaBloqueado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    // Si se indica, el bloqueo abarca desde $fecha hasta $fechaFin inclusive (vacaciones, etc.)
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fechaFin = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $motivo = null;

    // Relación con Local por si tienes varios locales en el futuro
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Local $local = null;

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $fechaFin): static
    {
        $this->fechaFin = $fechaFin;
        return $this;
    }

    public function esRango(): bool
    {
        return $this->fechaFin !== null && $this->fechaFin > $this->fecha;
    }

    public function getMotivo(): ?string
    {
        return $this->motivo;
    }

    public function setMotivo(?string $motivo): static
    {
        $this->motivo = $motivo;
        return $this;
    }

    public function getLocal(): ?Local
    {
        return $this->local;
    }

    public function setLocal(?Local $local): static
    {
        $this->local = $local;
        return $this;
    }
}