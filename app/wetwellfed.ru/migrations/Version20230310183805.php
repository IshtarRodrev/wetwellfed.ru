<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310183805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, food_id INT NOT NULL, eater_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_64C19C1BA8E87C4 (food_id), INDEX IDX_64C19C149BFF538 (eater_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1BA8E87C4 FOREIGN KEY (food_id) REFERENCES food (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C149BFF538 FOREIGN KEY (eater_id) REFERENCES eater (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1BA8E87C4');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C149BFF538');
        $this->addSql('DROP TABLE category');
    }
}
