<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429063603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE regla_horario (id INT AUTO_INCREMENT NOT NULL, dia_semana INT NOT NULL, hora_desde TIME DEFAULT NULL, hora_hasta TIME DEFAULT NULL, motivo VARCHAR(255) DEFAULT NULL, local_id INT NOT NULL, INDEX IDX_6F330B405D5A2101 (local_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE regla_horario ADD CONSTRAINT FK_6F330B405D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE regla_horario DROP FOREIGN KEY FK_6F330B405D5A2101');
        $this->addSql('DROP TABLE regla_horario');
    }
}
