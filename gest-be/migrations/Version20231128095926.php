<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231128095926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $deliveries = $this->connection->fetchAllAssociative(
            'SELECT 
                id, customer_id, week_day, delivery_week_day, weeks, notes, payment_method
            FROM delivery
            '
        );
        $writeDelivery = $this->connection->prepare('INSERT INTO delivery 
            (id, customer_id, week_day, delivery_week_day, harvest_date, delivery_date, notes, payment_method) 
            VALUES (nextval(\'delivery_id_seq\'), ?, ?, ?, ?, ?, ?, ?) RETURNING id
        ');

        $readProducts = $this->connection->prepare('SELECT id, product_id, qty FROM delivery_product WHERE delivery_id = ?');

        $writeProducst = $this->connection->prepare('INSERT INTO delivery_product 
        (id, delivery_id, product_id, qty)
         VALUES (nextval(\'delivery_product_id_seq\'), ?, ?, ?)');

        foreach ($deliveries as $delivery) {
            $weeks = explode(',', $delivery['weeks']);
            $products = $readProducts->executeQuery([$delivery['id']])->fetchAllAssociative();
            foreach ($weeks as $week) {
                $date = new \DateTimeImmutable();
                $harvestDate = $date->setIsoDate(2023, $week - 1, $delivery['week_day']);
                $deliveryDate = $date->setIsoDate(2023, $week - 1, $delivery['delivery_week_day']);
                $id = $writeDelivery->executeQuery([
                    $delivery['customer_id'],
                    $delivery['week_day'],
                    $delivery['delivery_week_day'],
                    $harvestDate->format('Y-m-d'),
                    $deliveryDate->format('Y-m-d'),
                    $delivery['notes'],
                    $delivery['payment_method'],
                ])->fetchOne();
                foreach ($products as $product) {
                    $writeProducst->executeStatement([
                        $id,
                        $product['product_id'],
                        $product['qty'],
                    ]);
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
    }
}
