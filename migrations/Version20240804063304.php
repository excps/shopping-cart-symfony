<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240804063304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__cart_item AS SELECT id, cart_id_id, code, name, price, quantity FROM cart_item');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('CREATE TABLE cart_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id INTEGER NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cart_item (id, cart_id, code, name, price, quantity) SELECT id, cart_id_id, code, name, price, quantity FROM __temp__cart_item');
        $this->addSql('DROP TABLE __temp__cart_item');
        $this->addSql('CREATE INDEX IDX_F0FE25271AD5CDBF ON cart_item (cart_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__cart_item AS SELECT id, cart_id, code, name, price, quantity FROM cart_item');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('CREATE TABLE cart_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id_id INTEGER NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_F0FE252720AEF35F FOREIGN KEY (cart_id_id) REFERENCES cart (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cart_item (id, cart_id_id, code, name, price, quantity) SELECT id, cart_id, code, name, price, quantity FROM __temp__cart_item');
        $this->addSql('DROP TABLE __temp__cart_item');
        $this->addSql('CREATE INDEX IDX_F0FE252720AEF35F ON cart_item (cart_id_id)');
    }
}
