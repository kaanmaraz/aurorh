<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221029202729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mail_envoye (id INT AUTO_INCREMENT NOT NULL, candidat_id INT NOT NULL, contenu LONGTEXT DEFAULT NULL, sujet VARCHAR(255) DEFAULT NULL, date_envoi DATETIME DEFAULT NULL, INDEX IDX_DC3A6D388D0EB82 (candidat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail_envoye_piece_jointe (mail_envoye_id INT NOT NULL, piece_jointe_id INT NOT NULL, INDEX IDX_95655C865A66BD9C (mail_envoye_id), INDEX IDX_95655C86A3741A05 (piece_jointe_id), PRIMARY KEY(mail_envoye_id, piece_jointe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mail_envoye ADD CONSTRAINT FK_DC3A6D388D0EB82 FOREIGN KEY (candidat_id) REFERENCES candidat (id)');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe ADD CONSTRAINT FK_95655C865A66BD9C FOREIGN KEY (mail_envoye_id) REFERENCES mail_envoye (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe ADD CONSTRAINT FK_95655C86A3741A05 FOREIGN KEY (piece_jointe_id) REFERENCES piece_jointe (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mail_envoye DROP FOREIGN KEY FK_DC3A6D388D0EB82');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe DROP FOREIGN KEY FK_95655C865A66BD9C');
        $this->addSql('ALTER TABLE mail_envoye_piece_jointe DROP FOREIGN KEY FK_95655C86A3741A05');
        $this->addSql('DROP TABLE mail_envoye');
        $this->addSql('DROP TABLE mail_envoye_piece_jointe');
    }
}
