<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213104808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, marca VARCHAR(255) NOT NULL, descripcion LONGTEXT DEFAULT NULL, precio DOUBLE PRECISION NOT NULL, imagen VARCHAR(255) DEFAULT NULL, local_id INT NOT NULL, INDEX IDX_A7BB06155D5A2101 (local_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB06155D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
        $this->addSql('ALTER TABLE cita ADD empleado_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cita ADD CONSTRAINT FK_3E379A62952BE730 FOREIGN KEY (empleado_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3E379A62952BE730 ON cita (empleado_id)');
        $this->addSql('ALTER TABLE horario DROP dia_semana, DROP hora_cierre, CHANGE hora_apertura hora_apertura DATETIME NOT NULL');
        $this->addSql('ALTER TABLE servicio ADD local_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A5D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
        $this->addSql('CREATE INDEX IDX_CB86F22A5D5A2101 ON servicio (local_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06155D5A2101');
        $this->addSql('DROP TABLE producto');
        $this->addSql('ALTER TABLE cita DROP FOREIGN KEY FK_3E379A62952BE730');
        $this->addSql('DROP INDEX IDX_3E379A62952BE730 ON cita');
        $this->addSql('ALTER TABLE cita DROP empleado_id');
        $this->addSql('ALTER TABLE horario ADD dia_semana INT NOT NULL, ADD hora_cierre TIME NOT NULL, CHANGE hora_apertura hora_apertura TIME NOT NULL');
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A5D5A2101');
        $this->addSql('DROP INDEX IDX_CB86F22A5D5A2101 ON servicio');
        $this->addSql('ALTER TABLE servicio DROP local_id');
    }
}
