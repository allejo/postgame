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

final class Version20190527085129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pause_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_EAB1B862186CE3E1 (replay_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resume_event (id INT AUTO_INCREMENT NOT NULL, replay_id INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_A759CACF186CE3E1 (replay_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pause_event ADD CONSTRAINT FK_EAB1B862186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
        $this->addSql('ALTER TABLE resume_event ADD CONSTRAINT FK_A759CACF186CE3E1 FOREIGN KEY (replay_id) REFERENCES replay (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pause_event');
        $this->addSql('DROP TABLE resume_event');
    }
}
