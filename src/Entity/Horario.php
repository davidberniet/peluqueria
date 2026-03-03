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

    // Cambiado a DATETIME (fecha y hora) según tu esquema final
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $horaApertura = null;

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