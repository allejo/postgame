<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190513031401 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE capture_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, capper_id INT NOT NULL, capper_team INT NOT NULL, capped_team INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_83A193C9186CE3E1 (replay_id), INDEX IDX_83A193C971085808 (capper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, player_id INT DEFAULT NULL, target_id INT DEFAULT NULL, team_from INT NOT NULL, team_to INT NOT NULL, message VARCHAR(128) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FAB3FC16186CE3E1 (replay_id), INDEX IDX_FAB3FC1699E6F5DF (player_id), INDEX IDX_FAB3FC16158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flag_update (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, player_id INT NOT NULL, is_grab TINYINT(1) NOT NULL, flag_abbv VARCHAR(3) NOT NULL, pos_x DOUBLE PRECISION NOT NULL, pos_y DOUBLE PRECISION NOT NULL, pos_z DOUBLE PRECISION NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_757138A9186CE3E1 (replay_id), INDEX IDX_757138A999E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE join_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, player_id INT NOT NULL, team INT NOT NULL, motto VARCHAR(128) NOT NULL, ip_address VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_B2EC790A186CE3E1 (replay_id), INDEX IDX_B2EC790A99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kill_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, victim_id INT NOT NULL, killer_id INT DEFAULT NULL, victim_team INT NOT NULL, killer_team INT DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_F14B74F3186CE3E1 (replay_id), INDEX IDX_F14B74F344972A0E (victim_id), INDEX IDX_F14B74F3CD5FD5FF (killer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, player_id INT NOT NULL, join_event_id INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_A8DD5D44186CE3E1 (replay_id), INDEX IDX_A8DD5D4499E6F5DF (player_id), UNIQUE INDEX UNIQ_A8DD5D443C972CB8 (join_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, callsign VARCHAR(32) NOT NULL, INDEX IDX_98197A65186CE3E1 (replay_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE replay (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, duration INT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C971085808 FOREIGN KEY (capper_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC1699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16158E0B66 FOREIGN KEY (target_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A999E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F3186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F344972A0E FOREIGN KEY (victim_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F3CD5FD5FF FOREIGN KEY (killer_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D44186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D4499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D443C972CB8 FOREIGN KEY (join_event_id) REFERENCES join_event (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D443C972CB8');
        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C971085808');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1699E6F5DF');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16158E0B66');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A999E6F5DF');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A99E6F5DF');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F344972A0E');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F3CD5FD5FF');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D4499E6F5DF');
        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C9186CE3E1');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16186CE3E1');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A9186CE3E1');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A186CE3E1');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F3186CE3E1');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D44186CE3E1');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65186CE3E1');
        $this->addSql('DROP TABLE capture_event');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE flag_update');
        $this->addSql('DROP TABLE join_event');
        $this->addSql('DROP TABLE kill_event');
        $this->addSql('DROP TABLE part_event');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE replay');
    }
}
