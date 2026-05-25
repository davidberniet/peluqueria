<?php

namespace App\Entity;

use App\Repository\LocalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalRepository::class)]
class Local
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $direccion = null;

    #[ORM\Column(length: 100)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $activo = true;

    /**
     * @var Collection<int, Horario>
     */
    #[ORM\OneToMany(targetEntity: Horario::class, mappedBy: 'local', orphanRemoval: true)]
    private Collection $horarios;

    /**
     * @var Collection<int, Cita>
     */
    #[ORM\OneToMany(targetEntity: Cita::class, mappedBy: 'local', orphanRemoval: true)]
    private Collection $citas;

    /**
     * @var Collection<int, Producto>
     */
    #[ORM\ManyToMany(targetEntity: Producto::class, mappedBy: 'locales')]
    private Collection $productos;

    /**
     * @var Collection<int, Servicio>
     */
    #[ORM\OneToMany(targetEntity: Servicio::class, mappedBy: 'local')]
    private Collection $servicios;

    /**
     * @var Collection<int, ReglaHorario>
     */
    #[ORM\OneToMany(targetEntity: ReglaHorario::class, mappedBy: 'local')]
    private Collection $reglaHorarios;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'local')]
    private Collection $empleados;

    public function __construct()
    {
        $this->horarios = new ArrayCollection();
        $this->citas = new ArrayCollection();
        $this->productos = new ArrayCollection();
        $this->servicios = new ArrayCollection();
        $this->reglaHorarios = new ArrayCollection();
        $this->empleados = new ArrayCollection();
    }

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

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): static
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    /**
     * @return Collection<int, Horario>
     */
    public function getHorarios(): Collection
    {
        return $this->horarios;
    }

    public function addHorario(Horario $horario): static
    {
        if (!$this->horarios->contains($horario)) {
            $this->horarios->add($horario);
            $horario->setLocal($this);
        }

        return $this;
    }

    public function removeHorario(Horario $horario): static
    {
        if ($this->horarios->removeElement($horario)) {
if ($horario->getLocal() === $this) {
                $horario->setLocal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cita>
     */
    public function getCitas(): Collection
    {
        return $this->citas;
    }

    public function addCita(Cita $cita): static
    {
        if (!$this->citas->contains($cita)) {
            $this->citas->add($cita);
            $cita->setLocal($this);
        }

        return $this;
    }

    public function removeCita(Cita $cita): static
    {
        if ($this->citas->removeElement($cita)) {
if ($cita->getLocal() === $this) {
                $cita->setLocal(null);
            }
        }

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
            $producto->addLocale($this);
        }

        return $this;
    }

    public function removeProducto(Producto $producto): static
    {
        if ($this->productos->removeElement($producto)) {
            $producto->removeLocale($this);
        }

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
            $servicio->setLocal($this);
        }

        return $this;
    }

    public function removeServicio(Servicio $servicio): static
    {
        if ($this->servicios->removeElement($servicio)) {
if ($servicio->getLocal() === $this) {
                $servicio->setLocal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReglaHorario>
     */
    public function getReglaHorarios(): Collection
    {
        return $this->reglaHorarios;
    }

    public function addReglaHorario(ReglaHorario $reglaHorario): static
    {
        if (!$this->reglaHorarios->contains($reglaHorario)) {
            $this->reglaHorarios->add($reglaHorario);
            $reglaHorario->setLocal($this);
        }

        return $this;
    }

    public function removeReglaHorario(ReglaHorario $reglaHorario): static
    {
        if ($this->reglaHorarios->removeElement($reglaHorario)) {
if ($reglaHorario->getLocal() === $this) {
                $reglaHorario->setLocal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEmpleados(): Collection
    {
        return $this->empleados;
    }

    public function addEmpleado(User $empleado): static
    {
        if (!$this->empleados->contains($empleado)) {
            $this->empleados->add($empleado);
            $empleado->setLocal($this);
        }

        return $this;
    }

    public function removeEmpleado(User $empleado): static
    {
        if ($this->empleados->removeElement($empleado)) {
if ($empleado->getLocal() === $this) {
                $empleado->setLocal(null);
            }
        }

        return $this;
    }
}
