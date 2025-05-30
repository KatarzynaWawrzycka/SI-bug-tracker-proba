<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501141651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bugs ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_1E197C9F675F31B ON bugs (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bugs DROP FOREIGN KEY FK_1E197C9F675F31B');
        $this->addSql('DROP INDEX IDX_1E197C9F675F31B ON bugs');
        $this->addSql('ALTER TABLE bugs DROP author_id');
    }
}
