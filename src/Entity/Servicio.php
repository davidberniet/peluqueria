<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServicioRepository::class)]
class Servicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El nombre del servicio no puede estar vacío.')]
    #[Assert\Length(max: 255, maxMessage: 'El nombre no puede superar {{ limit }} caracteres.')]
    private ?string $nombre = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La duración es obligatoria.')]
    #[Assert\Positive(message: 'La duración debe ser un valor positivo (en minutos).')]
    private ?int $duration = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'El precio es obligatorio.')]
    #[Assert\Positive(message: 'El precio debe ser un valor positivo.')]
    private ?float $precio = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La categoría no puede estar vacía.')]
    #[Assert\Length(max: 100, maxMessage: 'La categoría no puede superar {{ limit }} caracteres.')]
    private ?string $categoria = null;

    #[ORM\Column]
    private ?bool $activo = true;

    #[ORM\ManyToOne(inversedBy: 'servicios')]
    private ?Local $local = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

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
