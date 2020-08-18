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

final class Version20200815230755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE known_map (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, slug VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE map_thumbnail ADD known_map_id INT DEFAULT NULL, DROP filename');
        $this->addSql('ALTER TABLE map_thumbnail ADD CONSTRAINT FK_663F61BB666297A6 FOREIGN KEY (known_map_id) REFERENCES known_map (id)');
        $this->addSql('CREATE INDEX IDX_663F61BB666297A6 ON map_thumbnail (known_map_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE map_thumbnail DROP FOREIGN KEY FK_663F61BB666297A6');
        $this->addSql('DROP TABLE known_map');
        $this->addSql('DROP INDEX IDX_663F61BB666297A6 ON map_thumbnail');
        $this->addSql('ALTER TABLE map_thumbnail ADD filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP known_map_id');
    }
}
