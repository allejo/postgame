<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190609054827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C9186CE3E1');
        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C971085808');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C971085808 FOREIGN KEY (capper_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16186CE3E1');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A9186CE3E1');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A999E6F5DF');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A999E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A186CE3E1');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A99E6F5DF');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F3186CE3E1');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F344972A0E');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F3186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F344972A0E FOREIGN KEY (victim_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D44186CE3E1');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D443C972CB8');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D4499E6F5DF');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D44186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D443C972CB8 FOREIGN KEY (join_event_id) REFERENCES join_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D4499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pause_event DROP FOREIGN KEY FK_EAB1B862186CE3E1');
        $this->addSql('ALTER TABLE pause_event ADD CONSTRAINT FK_EAB1B862186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65186CE3E1');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_event DROP FOREIGN KEY FK_A759CACF186CE3E1');
        $this->addSql('ALTER TABLE resume_event ADD CONSTRAINT FK_A759CACF186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C9186CE3E1');
        $this->addSql('ALTER TABLE capture_event DROP FOREIGN KEY FK_83A193C971085808');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE capture_event ADD CONSTRAINT FK_83A193C971085808 FOREIGN KEY (capper_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16186CE3E1');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A9186CE3E1');
        $this->addSql('ALTER TABLE flag_update DROP FOREIGN KEY FK_757138A999E6F5DF');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A9186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE flag_update ADD CONSTRAINT FK_757138A999E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A186CE3E1');
        $this->addSql('ALTER TABLE join_event DROP FOREIGN KEY FK_B2EC790A99E6F5DF');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE join_event ADD CONSTRAINT FK_B2EC790A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F3186CE3E1');
        $this->addSql('ALTER TABLE kill_event DROP FOREIGN KEY FK_F14B74F344972A0E');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F3186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE kill_event ADD CONSTRAINT FK_F14B74F344972A0E FOREIGN KEY (victim_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D44186CE3E1');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D4499E6F5DF');
        $this->addSql('ALTER TABLE part_event DROP FOREIGN KEY FK_A8DD5D443C972CB8');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D44186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D4499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE part_event ADD CONSTRAINT FK_A8DD5D443C972CB8 FOREIGN KEY (join_event_id) REFERENCES join_event (id)');
        $this->addSql('ALTER TABLE pause_event DROP FOREIGN KEY FK_EAB1B862186CE3E1');
        $this->addSql('ALTER TABLE pause_event ADD CONSTRAINT FK_EAB1B862186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65186CE3E1');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE resume_event DROP FOREIGN KEY FK_A759CACF186CE3E1');
        $this->addSql('ALTER TABLE resume_event ADD CONSTRAINT FK_A759CACF186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
    }
}
