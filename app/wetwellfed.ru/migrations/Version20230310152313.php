<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310152313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food ADD eater_id INT NOT NULL');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT FK_D43829F749BFF538 FOREIGN KEY (eater_id) REFERENCES eater (id)');
        $this->addSql('CREATE INDEX IDX_D43829F749BFF538 ON food (eater_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY FK_D43829F749BFF538');
        $this->addSql('DROP INDEX IDX_D43829F749BFF538 ON food');
        $this->addSql('ALTER TABLE food DROP eater_id');
    }
}
