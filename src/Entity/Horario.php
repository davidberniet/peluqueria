<?php

namespace App\Entity;

use App\Repository\HorarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HorarioRepository::class)]
class Horario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaApertura = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $horaCierre = null;

    #[ORM\Column(options: ['default' => 30])]
    private int $intervaloMinutos = 30;

    #[ORM\ManyToOne(inversedBy: 'horarios')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Local $local = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHoraApertura(): ?\DateTimeInterface
    {
        return $this->horaApertura;
    }

    public function setHoraApertura(\DateTimeInterface $horaApertura): static
    {
        $this->horaApertura = $horaApertura;
        return $this;
    }

    public function getHoraCierre(): ?\DateTimeInterface
    {
        return $this->horaCierre;
    }

    public function setHoraCierre(\DateTimeInterface $horaCierre): static
    {
        $this->horaCierre = $horaCierre;
        return $this;
    }

    public function getIntervaloMinutos(): int
    {
        return $this->intervaloMinutos;
    }

    public function setIntervaloMinutos(int $intervaloMinutos): static
    {
        $this->intervaloMinutos = $intervaloMinutos;
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