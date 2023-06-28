<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628113741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD payment_method VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD price INT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ALTER customer_id DROP NOT NULL');
        $this->addSql('ALTER TABLE horder RENAME COLUMN grams TO decigrams');
        $this->addSql('ALTER TABLE product RENAME COLUMN grams TO decigrams');
        $this->addSql('ALTER TABLE "user" ADD sdi VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD zone VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product RENAME COLUMN decigrams TO grams');
        $this->addSql('ALTER TABLE delivery DROP payment_method');
        $this->addSql('ALTER TABLE delivery DROP price');
        $this->addSql('ALTER TABLE delivery ALTER customer_id SET NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP sdi');
        $this->addSql('ALTER TABLE "user" DROP zone');
        $this->addSql('ALTER TABLE horder RENAME COLUMN decigrams TO grams');
    }
}
