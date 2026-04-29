<?php

namespace App\Entity;

use App\Repository\ReglaHorarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReglaHorarioRepository::class)]
class ReglaHorario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $diaSemana = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $horaDesde = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $horaHasta = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motivo = null;

    #[ORM\ManyToOne(inversedBy: 'reglaHorarios')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Local $local = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaSemana(): ?int
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(int $diaSemana): static
    {
        $this->diaSemana = $diaSemana;

        return $this;
    }

    public function getHoraDesde(): ?\DateTime
    {
        return $this->horaDesde;
    }

    public function setHoraDesde(?\DateTime $horaDesde): static
    {
        $this->horaDesde = $horaDesde;

        return $this;
    }

    public function getHoraHasta(): ?\DateTime
    {
        return $this->horaHasta;
    }

    public function setHoraHasta(?\DateTime $horaHasta): static
    {
        $this->horaHasta = $horaHasta;

        return $this;
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
