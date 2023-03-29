<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310194223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1BA8E87C4');
        $this->addSql('DROP INDEX IDX_64C19C1BA8E87C4 ON category');
        $this->addSql('ALTER TABLE category DROP food_id');
        $this->addSql('ALTER TABLE food ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT FK_D43829F712469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_D43829F712469DE2 ON food (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD food_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1BA8E87C4 FOREIGN KEY (food_id) REFERENCES food (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1BA8E87C4 ON category (food_id)');
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY FK_D43829F712469DE2');
        $this->addSql('DROP INDEX IDX_D43829F712469DE2 ON food');
        $this->addSql('ALTER TABLE food DROP category_id');
    }
}
