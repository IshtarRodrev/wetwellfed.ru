<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621210152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY category_ibfk_3');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY category_ibfk_2');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C149BFF538 FOREIGN KEY (eater_id) REFERENCES eater (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY food_ibfk_1');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT FK_D43829F712469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C149BFF538');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT category_ibfk_3 FOREIGN KEY (eater_id) REFERENCES eater (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT category_ibfk_2 FOREIGN KEY (parent_id) REFERENCES category (id) ON UPDATE SET NULL ON DELETE SET NULL');
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY FK_D43829F712469DE2');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT food_ibfk_1 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE CASCADE ON DELETE SET NULL');
    }
}
