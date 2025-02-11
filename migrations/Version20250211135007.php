<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211135007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE extract (token_code VARCHAR(32) NOT NULL, token TEXT NOT NULL, response JSONB DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT DEFAULT NULL, next_token TEXT DEFAULT NULL, latency INT DEFAULT NULL, errors INT DEFAULT NULL, remaining INT DEFAULT NULL, resume TEXT DEFAULT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(token_code))');
        $this->addSql('COMMENT ON COLUMN extract.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE extract');
    }
}
