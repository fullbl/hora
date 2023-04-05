<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405094633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE activity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE delivery_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE horder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE step_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE storage_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE suspension_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE activity (id INT NOT NULL, delivery_id INT NOT NULL, step_id INT NOT NULL, executer_id INT NOT NULL, week SMALLINT NOT NULL, workable_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, workable_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(10) NOT NULL, data JSON NOT NULL, execution_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC74095A12136921 ON activity (delivery_id)');
        $this->addSql('CREATE INDEX IDX_AC74095A73B21E9C ON activity (step_id)');
        $this->addSql('CREATE INDEX IDX_AC74095AC00D111A ON activity (executer_id)');
        $this->addSql('COMMENT ON COLUMN activity.workable_from IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN activity.workable_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN activity.execution_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE delivery (id INT NOT NULL, customer_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3781EC109395C3F3 ON delivery (customer_id)');
        $this->addSql('CREATE TABLE horder (id INT NOT NULL, product_id INT NOT NULL, status VARCHAR(10) NOT NULL, quantity SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_666E48114584665A ON horder (product_id)');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, storage_id INT NOT NULL, name VARCHAR(100) NOT NULL, grams INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD5CC5DB90 ON product (storage_id)');
        $this->addSql('CREATE TABLE step (id INT NOT NULL, product_id INT NOT NULL, name VARCHAR(10) NOT NULL, minutes SMALLINT NOT NULL, params JSON NOT NULL, sort SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43B9FE3C4584665A ON step (product_id)');
        $this->addSql('CREATE UNIQUE INDEX step_name_product ON step (name, product_id)');
        $this->addSql('CREATE TABLE storage (id INT NOT NULL, type VARCHAR(15) NOT NULL, grams SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX storage_type ON storage (type)');
        $this->addSql('CREATE TABLE suspension (id INT NOT NULL, prisoner_id INT NOT NULL, start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, stop TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82AF0500F3E2C918 ON suspension (prisoner_id)');
        $this->addSql('COMMENT ON COLUMN suspension.start IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN suspension.stop IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, status VARCHAR(10) NOT NULL, full_name VARCHAR(255) NOT NULL, vat_number VARCHAR(15) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX user_username ON "user" (username)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A12136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A73B21E9C FOREIGN KEY (step_id) REFERENCES step (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AC00D111A FOREIGN KEY (executer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC109395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE horder ADD CONSTRAINT FK_666E48114584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD5CC5DB90 FOREIGN KEY (storage_id) REFERENCES storage (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3C4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500F3E2C918 FOREIGN KEY (prisoner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE activity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE delivery_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE horder_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE step_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE storage_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE suspension_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE activity DROP CONSTRAINT FK_AC74095A12136921');
        $this->addSql('ALTER TABLE activity DROP CONSTRAINT FK_AC74095A73B21E9C');
        $this->addSql('ALTER TABLE activity DROP CONSTRAINT FK_AC74095AC00D111A');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC109395C3F3');
        $this->addSql('ALTER TABLE horder DROP CONSTRAINT FK_666E48114584665A');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD5CC5DB90');
        $this->addSql('ALTER TABLE step DROP CONSTRAINT FK_43B9FE3C4584665A');
        $this->addSql('ALTER TABLE suspension DROP CONSTRAINT FK_82AF0500F3E2C918');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE horder');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE step');
        $this->addSql('DROP TABLE storage');
        $this->addSql('DROP TABLE suspension');
        $this->addSql('DROP TABLE "user"');
    }
}
