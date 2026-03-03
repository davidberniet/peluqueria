<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219103617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cita_producto (cita_id INT NOT NULL, producto_id INT NOT NULL, INDEX IDX_161ABF6F1E011DDF (cita_id), INDEX IDX_161ABF6F7645698E (producto_id), PRIMARY KEY (cita_id, producto_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cita_producto ADD CONSTRAINT FK_161ABF6F1E011DDF FOREIGN KEY (cita_id) REFERENCES cita (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cita_producto ADD CONSTRAINT FK_161ABF6F7645698E FOREIGN KEY (producto_id) REFERENCES producto (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cita_producto DROP FOREIGN KEY FK_161ABF6F1E011DDF');
        $this->addSql('ALTER TABLE cita_producto DROP FOREIGN KEY FK_161ABF6F7645698E');
        $this->addSql('DROP TABLE cita_producto');
    }
}
