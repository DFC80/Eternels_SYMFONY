<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260726000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma initial : membres, activités, adhésions, cotisations, événements, équipements, jeux, repas, photos, consommations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE activity (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                type VARCHAR(50) NOT NULL,
                cover_image VARCHAR(255) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) DEFAULT NULL,
                address VARCHAR(255) DEFAULT NULL,
                is_verified TINYINT(1) NOT NULL DEFAULT 0,
                verification_token VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                avatar VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE membership (
                id INT AUTO_INCREMENT NOT NULL,
                member_id INT NOT NULL,
                activity_id INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_86FFD2857597D3FE (member_id),
                INDEX IDX_86FFD28581C06096 (activity_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE subscription_rate (
                id INT AUTO_INCREMENT NOT NULL,
                activity_id INT NOT NULL,
                label VARCHAR(100) NOT NULL,
                amount NUMERIC(8, 2) NOT NULL,
                period VARCHAR(30) NOT NULL DEFAULT 'annual',
                member_category VARCHAR(50) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_A86A9FA181C06096 (activity_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE subscription (
                id INT AUTO_INCREMENT NOT NULL,
                member_id INT NOT NULL,
                rate_id INT NOT NULL,
                amount_paid NUMERIC(8, 2) NOT NULL,
                payment_date DATE NOT NULL,
                valid_from DATE NOT NULL,
                valid_until DATE DEFAULT NULL,
                payment_method VARCHAR(50) NOT NULL DEFAULT 'cash',
                status VARCHAR(30) NOT NULL DEFAULT 'paid',
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_A3C664D37597D3FE (member_id),
                INDEX IDX_A3C664D38814B792 (rate_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE event (
                id INT AUTO_INCREMENT NOT NULL,
                activity_id INT DEFAULT NULL,
                title VARCHAR(200) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME DEFAULT NULL,
                location VARCHAR(255) DEFAULT NULL,
                max_participants INT DEFAULT NULL,
                price NUMERIC(8, 2) DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'planned',
                cover_image VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_3BAE0AA781C06096 (activity_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE meal (
                id INT AUTO_INCREMENT NOT NULL,
                event_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                price NUMERIC(8, 2) NOT NULL,
                is_vegetarian TINYINT(1) NOT NULL DEFAULT 0,
                max_quantity INT DEFAULT NULL,
                INDEX IDX_9EF68E9C71F7E88B (event_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE event_participation (
                id INT AUTO_INCREMENT NOT NULL,
                event_id INT NOT NULL,
                member_id INT NOT NULL,
                selected_meal_id INT DEFAULT NULL,
                registered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                status VARCHAR(30) NOT NULL DEFAULT 'registered',
                notes LONGTEXT DEFAULT NULL,
                UNIQUE INDEX unique_participation (event_id, member_id),
                INDEX IDX_431DA29871F7E88B (event_id),
                INDEX IDX_431DA2987597D3FE (member_id),
                INDEX IDX_431DA298B30B0A4C (selected_meal_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE consumption_item (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(100) NOT NULL,
                category VARCHAR(50) NOT NULL,
                price NUMERIC(8, 2) NOT NULL,
                is_available TINYINT(1) NOT NULL DEFAULT 1,
                image VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE consumption (
                id INT AUTO_INCREMENT NOT NULL,
                member_id INT NOT NULL,
                item_id INT DEFAULT NULL,
                event_id INT DEFAULT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price NUMERIC(8, 2) NOT NULL,
                total_price NUMERIC(8, 2) NOT NULL,
                consumed_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                notes LONGTEXT DEFAULT NULL,
                INDEX IDX_DC957E807597D3FE (member_id),
                INDEX IDX_DC957E80126F525E (item_id),
                INDEX IDX_DC957E8071F7E88B (event_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE equipment (
                id INT AUTO_INCREMENT NOT NULL,
                assigned_to_id INT DEFAULT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                category VARCHAR(50) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                `condition` VARCHAR(30) NOT NULL DEFAULT 'good',
                purchase_price NUMERIC(8, 2) DEFAULT NULL,
                purchase_date DATE DEFAULT NULL,
                serial_number VARCHAR(100) DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                is_available TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_D338D583EF641AF1 (assigned_to_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE game (
                id INT AUTO_INCREMENT NOT NULL,
                activity_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                min_players INT DEFAULT NULL,
                max_players INT DEFAULT NULL,
                publisher VARCHAR(100) DEFAULT NULL,
                year INT DEFAULT NULL,
                difficulty VARCHAR(50) DEFAULT NULL,
                average_duration INT DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                is_available TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_232B318C81C06096 (activity_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE photo (
                id INT AUTO_INCREMENT NOT NULL,
                activity_id INT NOT NULL,
                event_id INT DEFAULT NULL,
                uploaded_by_id INT DEFAULT NULL,
                filename VARCHAR(255) NOT NULL,
                caption VARCHAR(255) DEFAULT NULL,
                uploaded_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                is_public TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_14B7841881C06096 (activity_id),
                INDEX IDX_14B7841871F7E88B (event_id),
                INDEX IDX_14B78418A2B28FE8 (uploaded_by_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Foreign keys
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD2857597D3FE FOREIGN KEY (member_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28581C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE subscription_rate ADD CONSTRAINT FK_A86A9FA181C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D37597D3FE FOREIGN KEY (member_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D38814B792 FOREIGN KEY (rate_id) REFERENCES subscription_rate (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA781C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_431DA29871F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_431DA2987597D3FE FOREIGN KEY (member_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_431DA298B30B0A4C FOREIGN KEY (selected_meal_id) REFERENCES meal (id)');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_DC957E807597D3FE FOREIGN KEY (member_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_DC957E80126F525E FOREIGN KEY (item_id) REFERENCES consumption_item (id)');
        $this->addSql('ALTER TABLE consumption ADD CONSTRAINT FK_DC957E8071F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583EF641AF1 FOREIGN KEY (assigned_to_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841881C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841871F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD2857597D3FE');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28581C06096');
        $this->addSql('ALTER TABLE subscription_rate DROP FOREIGN KEY FK_A86A9FA181C06096');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D37597D3FE');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D38814B792');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA781C06096');
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9C71F7E88B');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_431DA29871F7E88B');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_431DA2987597D3FE');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_431DA298B30B0A4C');
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_DC957E807597D3FE');
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_DC957E80126F525E');
        $this->addSql('ALTER TABLE consumption DROP FOREIGN KEY FK_DC957E8071F7E88B');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583EF641AF1');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C81C06096');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841881C06096');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841871F7E88B');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418A2B28FE8');

        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE consumption');
        $this->addSql('DROP TABLE consumption_item');
        $this->addSql('DROP TABLE event_participation');
        $this->addSql('DROP TABLE meal');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE subscription_rate');
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE activity');
    }
}
