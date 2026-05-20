<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520081452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE producto_local (producto_id INT NOT NULL, local_id INT NOT NULL, INDEX IDX_CBF180827645698E (producto_id), INDEX IDX_CBF180825D5A2101 (local_id), PRIMARY KEY (producto_id, local_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE producto_local ADD CONSTRAINT FK_CBF180827645698E FOREIGN KEY (producto_id) REFERENCES producto (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE producto_local ADD CONSTRAINT FK_CBF180825D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) ON DELETE CASCADE');
        
        // Copiar asociaciones existentes a la tabla intermedia
        $this->addSql('INSERT INTO producto_local (producto_id, local_id) SELECT id, local_id FROM producto WHERE local_id IS NOT NULL');

        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY `FK_A7BB06155D5A2101`');
        $this->addSql('DROP INDEX IDX_A7BB06155D5A2101 ON producto');
        $this->addSql('ALTER TABLE producto DROP local_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE producto_local DROP FOREIGN KEY FK_CBF180827645698E');
        $this->addSql('ALTER TABLE producto_local DROP FOREIGN KEY FK_CBF180825D5A2101');
        $this->addSql('DROP TABLE producto_local');
        $this->addSql('ALTER TABLE producto ADD local_id INT NOT NULL');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT `FK_A7BB06155D5A2101` FOREIGN KEY (local_id) REFERENCES local (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A7BB06155D5A2101 ON producto (local_id)');
    }
}
