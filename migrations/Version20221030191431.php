<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221030191431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe DROP FOREIGN KEY FK_95655C865A66BD9C');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe DROP FOREIGN KEY FK_95655C86A3741A05');
        $this->addSql('DROP TABLE mail_envoye_piece_jointe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mail_envoye_piece_jointe (mail_envoye_id INT NOT NULL, piece_jointe_id INT NOT NULL, INDEX IDX_95655C865A66BD9C (mail_envoye_id), INDEX IDX_95655C86A3741A05 (piece_jointe_id), PRIMARY KEY(mail_envoye_id, piece_jointe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe ADD CONSTRAINT FK_95655C865A66BD9C FOREIGN KEY (mail_envoye_id) REFERENCES mail_envoye (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe ADD CONSTRAINT FK_95655C86A3741A05 FOREIGN KEY (piece_jointe_id) REFERENCES piece_jointe (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
