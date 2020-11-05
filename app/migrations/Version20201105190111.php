<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105190111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, country_code VARCHAR(3) NOT NULL, full_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE public_holiday (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, year SMALLINT NOT NULL, month_day JSON NOT NULL, total_amount SMALLINT NOT NULL, INDEX IDX_8744ED86F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE public_holiday ADD CONSTRAINT FK_8744ED86F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE public_holiday DROP FOREIGN KEY FK_8744ED86F92F3E70');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE public_holiday');
    }
}
