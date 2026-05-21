<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El nombre del producto no puede estar vacío.')]
    #[Assert\Length(max: 255, maxMessage: 'El nombre no puede superar {{ limit }} caracteres.')]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La marca no puede estar vacía.')]
    #[Assert\Length(max: 255, maxMessage: 'La marca no puede superar {{ limit }} caracteres.')]
    private ?string $marca = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'El precio es obligatorio.')]
    #[Assert\Positive(message: 'El precio debe ser un valor positivo.')]
    private ?float $precio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagen = null;

    /**
     * @var Collection<int, Local>
     */
    #[ORM\ManyToMany(targetEntity: Local::class, inversedBy: 'productos')]
    private Collection $locales;

    public function __construct()
    {
        $this->locales = new ArrayCollection();
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

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

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

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): static
    {
        $this->imagen = $imagen;

        return $this;
    }

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: 'El stock no puede ser negativo.')]
    private ?int $stock = 0;

    /**
     * @return Collection<int, Local>
     */
    public function getLocales(): Collection
    {
        return $this->locales;
    }

    public function addLocale(Local $locale): static
    {
        if (!$this->locales->contains($locale)) {
            $this->locales->add($locale);
        }

        return $this;
    }

    public function removeLocale(Local $locale): static
    {
        $this->locales->removeElement($locale);

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }
}
