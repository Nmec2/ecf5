<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604141925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE presence DROP FOREIGN KEY FK_6977C7A5A40A2C8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE presence DROP FOREIGN KEY FK_6977C7A5DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE presence
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE presence (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, calendar_id INT NOT NULL, present TINYINT(1) NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_6977C7A5DD62C21B (child_id), INDEX IDX_6977C7A5A40A2C8 (calendar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE presence ADD CONSTRAINT FK_6977C7A5A40A2C8 FOREIGN KEY (calendar_id) REFERENCES calendar (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE presence ADD CONSTRAINT FK_6977C7A5DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
