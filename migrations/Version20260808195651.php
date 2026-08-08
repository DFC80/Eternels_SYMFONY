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
        $this->addSql('CREATE TABLE meeting (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, linked_event_id INTEGER DEFAULT NULL, type VARCHAR(30) NOT NULL, date DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, agenda CLOB DEFAULT NULL, notes CLOB DEFAULT NULL, is_published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_F515E1391FF7A654 FOREIGN KEY (linked_event_id) REFERENCES event (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F515E1391FF7A654 ON meeting (linked_event_id)');
        $this->addSql('ALTER TABLE event ADD COLUMN bureau_only BOOLEAN NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE meeting');
        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, title, description, start_date, end_date, location, max_participants, price, status, cover_image, created_at, activity_id FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description CLOB DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, max_participants INTEGER DEFAULT NULL, price NUMERIC(8, 2) DEFAULT NULL, status VARCHAR(30) NOT NULL, cover_image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, activity_id INTEGER DEFAULT NULL, CONSTRAINT FK_3BAE0AA781C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO event (id, title, description, start_date, end_date, location, max_participants, price, status, cover_image, created_at, activity_id) SELECT id, title, description, start_date, end_date, location, max_participants, price, status, cover_image, created_at, activity_id FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
        $this->addSql('CREATE INDEX IDX_3BAE0AA781C06096 ON event (activity_id)');
    }
}
