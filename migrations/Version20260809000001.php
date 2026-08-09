<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260809000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table market_listing pour la brocante airsoft entre membres';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE market_listing (
                id INT AUTO_INCREMENT NOT NULL,
                seller_id INT NOT NULL,
                type VARCHAR(20) NOT NULL DEFAULT 'VENTE',
                title VARCHAR(200) NOT NULL,
                description LONGTEXT NOT NULL,
                price NUMERIC(8, 2) DEFAULT NULL,
                category VARCHAR(50) DEFAULT NULL,
                photos JSON DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_market_seller (seller_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql('ALTER TABLE market_listing ADD CONSTRAINT FK_market_seller FOREIGN KEY (seller_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE market_listing DROP FOREIGN KEY FK_market_seller');
        $this->addSql('DROP TABLE market_listing');
    }
}
