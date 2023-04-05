<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405100035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO public."user" (id, username, roles, password, status, full_name, vat_number, email, address) VALUES (1, \'admin\', \'["ROLE_ADMIN"]\', \'$2y$13$4X/PoEGqiUbHMu/1s7aLteUywzCSXOdt9hjsbYTf27Njxb3ZpSlfi\', \'active\', \'admin\', \'000000\', \'admin@horafarms.it\', \'-\')');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (1, \'ground\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (2, \'seeds\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (3, \'seed_box\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (4, \'water_box\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (5, \'blackout_box\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (6, \'light_box\', 0)');
        $this->addSql('INSERT INTO public.storage (id, type, grams) VALUES (7, \'shipping_box\', 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
