<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129082835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cita (id INT AUTO_INCREMENT NOT NULL, fecha_inicio DATETIME NOT NULL, fecha_fin DATETIME NOT NULL, estado VARCHAR(20) NOT NULL, notas LONGTEXT DEFAULT NULL, usuario_id INT NOT NULL, local_id INT NOT NULL, INDEX IDX_3E379A62DB38439E (usuario_id), INDEX IDX_3E379A625D5A2101 (local_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cita_servicio (cita_id INT NOT NULL, servicio_id INT NOT NULL, INDEX IDX_7A274B501E011DDF (cita_id), INDEX IDX_7A274B5071CAA3E7 (servicio_id), PRIMARY KEY (cita_id, servicio_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE horario (id INT AUTO_INCREMENT NOT NULL, dia_semana INT NOT NULL, hora_apertura TIME NOT NULL, hora_cierre TIME NOT NULL, local_id INT NOT NULL, INDEX IDX_E25853A35D5A2101 (local_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE local (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, direccion VARCHAR(255) NOT NULL, ciudad VARCHAR(100) NOT NULL, telefono VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, activo TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE servicio (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, duration INT NOT NULL, precio DOUBLE PRECISION NOT NULL, categoria VARCHAR(100) NOT NULL, activo TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, telefono VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cita ADD CONSTRAINT FK_3E379A62DB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cita ADD CONSTRAINT FK_3E379A625D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
        $this->addSql('ALTER TABLE cita_servicio ADD CONSTRAINT FK_7A274B501E011DDF FOREIGN KEY (cita_id) REFERENCES cita (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cita_servicio ADD CONSTRAINT FK_7A274B5071CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horario ADD CONSTRAINT FK_E25853A35D5A2101 FOREIGN KEY (local_id) REFERENCES local (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cita DROP FOREIGN KEY FK_3E379A62DB38439E');
        $this->addSql('ALTER TABLE cita DROP FOREIGN KEY FK_3E379A625D5A2101');
        $this->addSql('ALTER TABLE cita_servicio DROP FOREIGN KEY FK_7A274B501E011DDF');
        $this->addSql('ALTER TABLE cita_servicio DROP FOREIGN KEY FK_7A274B5071CAA3E7');
        $this->addSql('ALTER TABLE horario DROP FOREIGN KEY FK_E25853A35D5A2101');
        $this->addSql('DROP TABLE cita');
        $this->addSql('DROP TABLE cita_servicio');
        $this->addSql('DROP TABLE horario');
        $this->addSql('DROP TABLE local');
        $this->addSql('DROP TABLE servicio');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
