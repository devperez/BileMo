<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration creates the necessary database tables for the customer, phone, and user entities.
 */
final class Version20231215093552 extends AbstractMigration
{
    /**
     * Gets the description of the migration.
     *
     * @return string The migration description.
     */
    public function getDescription(): string
    {
        return 'Auto-generated Migration for creating customer, phone, and user tables.';
    }

    /**
     * Executes the "up" migration.
     *
     * @param Schema $schema The schema object.
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_81398E09E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phone (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, screen_size VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', customer_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, user_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_8D93D6499395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    /**
     * Executes the "down" migration.
     *
     * @param Schema $schema The schema object.
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499395C3F3');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE phone');
        $this->addSql('DROP TABLE user');
    }
}
