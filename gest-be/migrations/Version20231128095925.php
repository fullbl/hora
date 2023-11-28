<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231128095925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE delivery ADD harvest_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD delivery_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ALTER COLUMN weeks DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN delivery.harvest_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN delivery.delivery_date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP harvest_date');
        $this->addSql('ALTER TABLE delivery DROP delivery_date');
        $this->addSql('ALTER TABLE delivery ALTER COLUMN weeks SET NOT NULL');
    }
}
