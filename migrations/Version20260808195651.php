<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808195651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table meeting et le champ bureau_only sur event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE meeting (
                id INT AUTO_INCREMENT NOT NULL,
                linked_event_id INT DEFAULT NULL,
                type VARCHAR(30) NOT NULL,
                date DATETIME NOT NULL,
                location VARCHAR(255) DEFAULT NULL,
                agenda LONGTEXT DEFAULT NULL,
                notes LONGTEXT DEFAULT NULL,
                is_published TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_F515E1391FF7A654 (linked_event_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E1391FF7A654 FOREIGN KEY (linked_event_id) REFERENCES event (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event ADD bureau_only TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E1391FF7A654');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('ALTER TABLE event DROP COLUMN bureau_only');
    }
}
