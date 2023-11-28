<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231128111059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM delivery_product USING delivery
            WHERE delivery.id = delivery_product.delivery_id
            AND harvest_date IS NULL');

        $this->addSql('TRUNCATE TABLE activity');

        $this->addSql('DELETE FROM delivery WHERE harvest_date IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
