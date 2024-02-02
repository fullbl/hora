<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202085212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_zone (user_id INT NOT NULL, zone_id INT NOT NULL)');
        $this->addSql('CREATE INDEX IDX_DA6A8CCEA76ED395 ON user_zone (user_id)');
        $this->addSql('CREATE INDEX IDX_DA6A8CCE9F2C3FAB ON user_zone (zone_id)');
        $this->addSql('CREATE TABLE zone (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('UPDATE "user" SET sub_zone = \'s_\' || sub_zone WHERE sub_zone = zone AND sub_zone IS NOT NULL');
        $this->addSql('INSERT INTO zone (id, name) SELECT NEXTVAL(\'zone_id_seq\'), zone FROM "user" WHERE zone IS NOT NULL GROUP BY zone');
        $this->addSql('INSERT INTO zone (id, parent_id, name) SELECT NEXTVAL(\'zone_id_seq\'), z.id, sub_zone FROM "user" LEFT JOIN zone z ON "user".zone = z.name WHERE sub_zone IS NOT NULL GROUP BY sub_zone, z.id');
        $this->addSql('CREATE INDEX IDX_A0EBC007727ACA70 ON zone (parent_id)');
        $this->addSql('ALTER TABLE user_zone ADD CONSTRAINT FK_DA6A8CCEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_zone ADD CONSTRAINT FK_DA6A8CCE9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_zone ADD PRIMARY KEY (user_id, zone_id)');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007727ACA70 FOREIGN KEY (parent_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO user_zone (user_id, zone_id) SELECT u.id, z.id FROM "user" u JOIN zone z ON z.name = u.zone');
        $this->addSql('INSERT INTO user_zone (user_id, zone_id) SELECT u.id, z.id FROM "user" u JOIN zone z ON z.name = u.sub_zone');
        $this->addSql('ALTER TABLE "user" DROP zone, DROP sub_zone');
        $this->addSql('CREATE TABLE product_zone (product_id INT NOT NULL, zone_id INT NOT NULL, PRIMARY KEY(product_id, zone_id))');
        $this->addSql('CREATE INDEX IDX_2D03E2A64584665A ON product_zone (product_id)');
        $this->addSql('CREATE INDEX IDX_2D03E2A69F2C3FAB ON product_zone (zone_id)');
        $this->addSql('ALTER TABLE product_zone ADD CONSTRAINT FK_2D03E2A64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_zone ADD CONSTRAINT FK_2D03E2A69F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD9F2C3FAB');
        $this->addSql('DROP SEQUENCE zone_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_zone DROP CONSTRAINT FK_DA6A8CCEA76ED395');
        $this->addSql('ALTER TABLE user_zone DROP CONSTRAINT FK_DA6A8CCE9F2C3FAB');
        $this->addSql('ALTER TABLE zone DROP CONSTRAINT FK_A0EBC007727ACA70');
        $this->addSql('DROP TABLE user_zone');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP INDEX IDX_D34A04AD9F2C3FAB');
        $this->addSql('ALTER TABLE "user" ADD zone VARCHAR(50), subzone VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_zone DROP CONSTRAINT FK_2D03E2A64584665A');
        $this->addSql('ALTER TABLE product_zone DROP CONSTRAINT FK_2D03E2A69F2C3FAB');
        $this->addSql('DROP TABLE product_zone');
    }
}
