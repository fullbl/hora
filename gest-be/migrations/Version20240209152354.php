<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240209152354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE delivery DROP week_day');
        $this->addSql('ALTER TABLE delivery DROP weeks');
        $this->addSql('ALTER TABLE delivery DROP delivery_week_day');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE delivery ADD week_day SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE delivery ADD weeks TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD delivery_week_day SMALLINT DEFAULT 1 NOT NULL');
    }
}
