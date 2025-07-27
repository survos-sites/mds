<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727121051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE extract (token_code VARCHAR(32) NOT NULL, grp_id VARCHAR(255) NOT NULL, token TEXT NOT NULL, response JSONB DEFAULT NULL, stats JSONB DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT DEFAULT NULL, next_token TEXT DEFAULT NULL, latency INT DEFAULT NULL, errors INT DEFAULT NULL, remaining INT DEFAULT NULL, resume TEXT DEFAULT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(token_code))');
        $this->addSql('CREATE INDEX IDX_3E5A31DED51E9150 ON extract (grp_id)');
        $this->addSql('COMMENT ON COLUMN extract.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE grp (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, start_token TEXT DEFAULT NULL, wikidata_id VARCHAR(50) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, aliases TEXT DEFAULT NULL, persistent_link VARCHAR(255) DEFAULT NULL, has_object_records BOOLEAN NOT NULL, description TEXT DEFAULT NULL, licence VARCHAR(255) DEFAULT NULL, count INT DEFAULT NULL, extract_count INT NOT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_processed_messages (id SERIAL NOT NULL, run_id INT NOT NULL, attempt SMALLINT NOT NULL, message_type VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, dispatched_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, received_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, wait_time BIGINT NOT NULL, handle_time BIGINT NOT NULL, memory_usage INT NOT NULL, transport VARCHAR(255) NOT NULL, tags VARCHAR(255) DEFAULT NULL, failure_type VARCHAR(255) DEFAULT NULL, failure_message TEXT DEFAULT NULL, results JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN messenger_processed_messages.dispatched_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_processed_messages.received_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_processed_messages.finished_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE record (id UUID NOT NULL, source_id INT NOT NULL, extract_id VARCHAR(32) NOT NULL, data JSONB NOT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9B349F91953C1C61 ON record (source_id)');
        $this->addSql('CREATE INDEX IDX_9B349F91ACB5626C ON record (extract_id)');
        $this->addSql('COMMENT ON COLUMN record.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE source (id SERIAL NOT NULL, record_count INT DEFAULT NULL, api_key TEXT DEFAULT NULL, expected_count INT DEFAULT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, org VARCHAR(255) DEFAULT NULL, grp VARCHAR(255) DEFAULT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX source_code ON source (code)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE extract ADD CONSTRAINT FK_3E5A31DED51E9150 FOREIGN KEY (grp_id) REFERENCES grp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91953C1C61 FOREIGN KEY (source_id) REFERENCES source (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91ACB5626C FOREIGN KEY (extract_id) REFERENCES extract (token_code) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE extract DROP CONSTRAINT FK_3E5A31DED51E9150');
        $this->addSql('ALTER TABLE record DROP CONSTRAINT FK_9B349F91953C1C61');
        $this->addSql('ALTER TABLE record DROP CONSTRAINT FK_9B349F91ACB5626C');
        $this->addSql('DROP TABLE extract');
        $this->addSql('DROP TABLE grp');
        $this->addSql('DROP TABLE messenger_processed_messages');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE source');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
