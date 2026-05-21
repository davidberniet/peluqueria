<?php

namespace App\Entity;

use App\Repository\CitaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CitaRepository::class)]
class Cita
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La fecha de inicio es obligatoria.')]
    #[Assert\Type(\DateTime::class)]
    private ?\DateTime $fechaInicio = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La fecha de fin es obligatoria.')]
    #[Assert\Type(\DateTime::class)]
    #[Assert\GreaterThan(propertyPath: 'fechaInicio', message: 'La fecha de fin debe ser posterior a la fecha de inicio.')]
    private ?\DateTime $fechaFin = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'El estado de la cita es obligatorio.')]
    #[Assert\Choice(
        choices: ['Pendiente', 'Confirmada', 'Cancelada', 'Completada'],
        message: 'El estado debe ser uno de: Pendiente, Confirmada, Cancelada, Completada.'
    )]
    private ?string $estado = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notas = null;

    #[ORM\ManyToOne(inversedBy: 'citas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'citas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Local $local = null;

    /**
     * @var Collection<int, Servicio>
     */
    #[ORM\ManyToMany(targetEntity: Servicio::class)]
    private Collection $servicios;

    #[ORM\ManyToOne(inversedBy: 'citasAtendidas')]
    private ?User $empleado = null;

    /**
     * @var Collection<int, Producto>
     */
    #[ORM\ManyToMany(targetEntity: Producto::class)]
    private Collection $productos;

    public function __construct()
    {
        $this->servicios = new ArrayCollection();
        $this->productos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaInicio(): ?\DateTime
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTime $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTime
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTime $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(?string $notas): static
    {
        $this->notas = $notas;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): static
    {
        $this->usuario = $usuario;

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

    /**
     * @return Collection<int, Servicio>
     */
    public function getServicios(): Collection
    {
        return $this->servicios;
    }

    public function addServicio(Servicio $servicio): static
    {
        if (!$this->servicios->contains($servicio)) {
            $this->servicios->add($servicio);
        }

        return $this;
    }

    public function removeServicio(Servicio $servicio): static
    {
        $this->servicios->removeElement($servicio);

        return $this;
    }

    public function getEmpleado(): ?User
    {
        return $this->empleado;
    }

    public function setEmpleado(?User $empleado): static
    {
        $this->empleado = $empleado;

        return $this;
    }

    /**
     * @return Collection<int, Producto>
     */
    public function getProductos(): Collection
    {
        return $this->productos;
    }

    public function addProducto(Producto $producto): static
    {
        if (!$this->productos->contains($producto)) {
            $this->productos->add($producto);
        }

        return $this;
    }

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $valoracion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comentarioValoracion = null;

    public function removeProducto(Producto $producto): static
    {
        $this->productos->removeElement($producto);

        return $this;
    }

    public function getValoracion(): ?int
    {
        return $this->valoracion;
    }

    public function setValoracion(?int $valoracion): static
    {
        $this->valoracion = $valoracion;

        return $this;
    }

    public function getComentarioValoracion(): ?string
    {
        return $this->comentarioValoracion;
    }

    public function setComentarioValoracion(?string $comentarioValoracion): static
    {
        $this->comentarioValoracion = $comentarioValoracion;

        return $this;
    }
}
