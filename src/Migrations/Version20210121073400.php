<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210121073400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player_heat_map (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, player_id INT NOT NULL, heatmap LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_890DB068186CE3E1 (replay_id), UNIQUE INDEX UNIQ_890DB06899E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_heat_map ADD CONSTRAINT FK_890DB068186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE player_heat_map ADD CONSTRAINT FK_890DB06899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE capture_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_message CHANGE sender_id sender_id INT DEFAULT NULL, CHANGE recipient_id recipient_id INT DEFAULT NULL, CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE flag_update CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE join_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kill_event CHANGE killer_id killer_id INT DEFAULT NULL, CHANGE killer_team killer_team INT DEFAULT NULL, CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE map_thumbnail CHANGE known_map_id known_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE part_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pause_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE replay CHANGE map_thumbnail_id map_thumbnail_id INT DEFAULT NULL, CHANGE file_hash file_hash VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE resume_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE player_heat_map');
        $this->addSql('ALTER TABLE capture_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_message CHANGE sender_id sender_id INT DEFAULT NULL, CHANGE recipient_id recipient_id INT DEFAULT NULL, CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE flag_update CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE join_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kill_event CHANGE killer_id killer_id INT DEFAULT NULL, CHANGE killer_team killer_team INT DEFAULT NULL, CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE map_thumbnail CHANGE known_map_id known_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE part_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pause_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE replay CHANGE map_thumbnail_id map_thumbnail_id INT DEFAULT NULL, CHANGE file_hash file_hash VARCHAR(40) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE resume_event CHANGE match_seconds match_seconds INT DEFAULT NULL');
    }
}
