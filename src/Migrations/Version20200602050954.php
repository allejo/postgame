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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200602050954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE map_thumbnail (id INT AUTO_INCREMENT NOT NULL, world_hash VARCHAR(40) NOT NULL, filename VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE replay ADD map_thumbnail_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE replay ADD CONSTRAINT FK_D937F4F265CAA37C FOREIGN KEY (map_thumbnail_id) REFERENCES map_thumbnail (id)');
        $this->addSql('CREATE INDEX IDX_D937F4F265CAA37C ON replay (map_thumbnail_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE replay DROP FOREIGN KEY FK_D937F4F265CAA37C');
        $this->addSql('DROP TABLE map_thumbnail');
        $this->addSql('DROP INDEX IDX_D937F4F265CAA37C ON replay');
        $this->addSql('ALTER TABLE replay DROP map_thumbnail_id');
    }
}
