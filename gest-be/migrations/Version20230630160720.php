<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630160720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity ADD qty SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE activity DROP data');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity ADD data JSON NOT NULL');
        $this->addSql('ALTER TABLE activity DROP qty');
    }
}
