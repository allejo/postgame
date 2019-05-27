<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190527091907 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE capture_event ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_message ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE flag_update ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE join_event ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kill_event ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE part_event ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pause_event ADD match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resume_event ADD match_seconds INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE capture_event DROP match_seconds');
        $this->addSql('ALTER TABLE chat_message DROP match_seconds');
        $this->addSql('ALTER TABLE flag_update DROP match_seconds');
        $this->addSql('ALTER TABLE join_event DROP match_seconds');
        $this->addSql('ALTER TABLE kill_event DROP match_seconds');
        $this->addSql('ALTER TABLE part_event DROP match_seconds');
        $this->addSql('ALTER TABLE pause_event DROP match_seconds');
        $this->addSql('ALTER TABLE resume_event DROP match_seconds');
    }
}
