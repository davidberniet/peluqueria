<?php

namespace App\Command;

use App\Repository\CitaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(
    name: 'app:enviar-recordatorios',
    description: 'Envía un email de recordatorio a los clientes con cita mañana',
)]
class EnviarRecordatoriosCitasCommand extends Command
{
    public function __construct(
        private CitaRepository $citaRepository,
        private MailerInterface $mailer,
        private Environment $twig
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Rango: mañana de 00:00 a 23:59
        $mananaInicio = new \DateTime('tomorrow 00:00:00', new \DateTimeZone('Europe/Madrid'));
        $mananaFin = new \DateTime('tomorrow 23:59:59', new \DateTimeZone('Europe/Madrid'));

        $citas = $this->citaRepository->createQueryBuilder('c')
            ->where('c.fechaInicio >= :inicio')
            ->andWhere('c.fechaInicio <= :fin')
            ->andWhere('c.estado != :cancelada')
            ->setParameter('inicio', $mananaInicio)
            ->setParameter('fin', $mananaFin)
            ->setParameter('cancelada', 'Cancelada')
            ->getQuery()
            ->getResult();

        if (empty($citas)) {
            $io->info('No hay citas para mañana. No se envió ningún recordatorio.');
            return Command::SUCCESS;
        }

        $enviados = 0;
        $errores = 0;

        foreach ($citas as $cita) {
            try {
                $html = $this->twig->render('emails/recordatorio_cita.html.twig', [
                    'cita' => $cita,
                ]);

                $email = (new Email())
                    ->from('venus.peluqueria@ejemplo.com') 
                    ->to($cita->getUsuario()->getEmail())
                    ->subject('✂️ Recordatorio: tu cita en Venus es mañana')
                    ->html($html);

                $this->mailer->send($email);
                $enviados++;

                $io->text("✅ Enviado a: {$cita->getUsuario()->getEmail()}");

            } catch (\Exception $e) {
                $errores++;
                $io->error("❌ Error con {$cita->getUsuario()->getEmail()}: {$e->getMessage()}");
            }
        }

        $io->success("Recordatorios enviados: {$enviados}. Errores: {$errores}.");
        return Command::SUCCESS;
    }
}