<?php

namespace App\Twig;

use App\Repository\MensajeContactoRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AdminExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private MensajeContactoRepository $repo) {}

    public function getGlobals(): array
    {
        return [
            'mensajesNoLeidos' => $this->repo->countNoLeidos(),
        ];
    }
}
