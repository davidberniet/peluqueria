<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428122859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dia_bloqueado (id INT AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, motivo VARCHAR(100) DEFAULT NULL, local_id INT NOT NULL, INDEX IDX_4D92D1A35D5A2101 (local_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dia_bloqueado ADD CONSTRAINT FK_4D92D1A35D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dia_bloqueado DROP FOREIGN KEY FK_4D92D1A35D5A2101');
        $this->addSql('DROP TABLE dia_bloqueado');
    }
}
