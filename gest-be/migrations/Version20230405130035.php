<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405130035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO public."user" (id, username, roles, password, status, full_name, vat_number, email, address) VALUES (1, \'admin\', \'["ROLE_ADMIN"]\', \'$2y$13$4X/PoEGqiUbHMu/1s7aLteUywzCSXOdt9hjsbYTf27Njxb3ZpSlfi\', \'active\', \'admin\', \'000000\', \'admin@horafarms.it\', \'-\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
