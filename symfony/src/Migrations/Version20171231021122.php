<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171231021122 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member ADD password VARCHAR(255) NOT NULL, CHANGE username username VARCHAR(25) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA78F85E0677 ON member (username)');
        $this->addSql('ALTER TABLE u2ftoken CHANGE member_id member_id INT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_70E4FA78F85E0677 ON member');
        $this->addSql('ALTER TABLE member DROP password, CHANGE username username VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE u2ftoken CHANGE member_id member_id INT DEFAULT NULL');
    }
}
