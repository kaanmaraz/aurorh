<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220903052558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidat (id INT AUTO_INCREMENT NOT NULL, type_candidat_id INT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(5) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, date_de_naissance DATE DEFAULT NULL, ville_naissance VARCHAR(255) DEFAULT NULL, departement_naissance VARCHAR(2) DEFAULT NULL, pays_naissance VARCHAR(255) DEFAULT NULL, numero_ss VARCHAR(15) DEFAULT NULL, nom_usage VARCHAR(255) DEFAULT NULL, completement_adresse VARCHAR(255) DEFAULT NULL, date_expiration_ts DATE DEFAULT NULL, sexe VARCHAR(10) DEFAULT NULL, email VARCHAR(255) NOT NULL, date_previs_embauche DATE DEFAULT NULL, poste VARCHAR(255) NOT NULL, site VARCHAR(255) NOT NULL, delai_formulaire DATE NOT NULL, mdp VARCHAR(255) DEFAULT NULL, numero_agent VARCHAR(255) DEFAULT NULL, debut_cdd DATE DEFAULT NULL, fin_cdd DATE DEFAULT NULL, service VARCHAR(255) DEFAULT NULL, coeff_developpe INT DEFAULT NULL, pts_garantie INT DEFAULT NULL, niveau_salaire VARCHAR(2) DEFAULT NULL, coeff_base INT DEFAULT NULL, pts_competences INT DEFAULT NULL, periode_essai VARCHAR(255) DEFAULT NULL, pts_experience INT DEFAULT NULL, prime VARCHAR(255) DEFAULT NULL, a_diplome TINYINT(1) DEFAULT NULL, nationnalite VARCHAR(255) DEFAULT NULL, type_nature INT DEFAULT NULL, type_referentiel VARCHAR(255) DEFAULT NULL, deja_complete TINYINT(1) DEFAULT NULL, supprime TINYINT(1) DEFAULT NULL, date_suppression DATE DEFAULT NULL, numero_agent_manager VARCHAR(5) DEFAULT NULL, cle VARCHAR(10) DEFAULT NULL, UNIQUE INDEX UNIQ_6AB5B471E7927C74 (email), UNIQUE INDEX UNIQ_6AB5B47189D4B7A0 (numero_agent), INDEX IDX_6AB5B47143C93C13 (type_candidat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, candidat_id INT NOT NULL, url VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, taille INT NOT NULL, INDEX IDX_D8698A768D0EB82 (candidat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_type_document (document_id INT NOT NULL, type_document_id INT NOT NULL, INDEX IDX_32390901C33F7837 (document_id), INDEX IDX_323909018826AFA6 (type_document_id), PRIMARY KEY(document_id, type_document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lien (id INT AUTO_INCREMENT NOT NULL, candidat_id INT NOT NULL, utilisateur_id INT NOT NULL, token VARCHAR(255) NOT NULL, date_limite DATE NOT NULL, date_envoi DATE DEFAULT NULL, UNIQUE INDEX UNIQ_A532B4B58D0EB82 (candidat_id), INDEX IDX_A532B4B5FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_candidat (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_document (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, obligatoire TINYINT(1) NOT NULL, multiple TINYINT(1) NOT NULL, format VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_document_type_candidat (type_document_id INT NOT NULL, type_candidat_id INT NOT NULL, INDEX IDX_5125A7338826AFA6 (type_document_id), INDEX IDX_5125A73343C93C13 (type_candidat_id), PRIMARY KEY(type_document_id, type_candidat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidat ADD CONSTRAINT FK_6AB5B47143C93C13 FOREIGN KEY (type_candidat_id) REFERENCES type_candidat (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768D0EB82 FOREIGN KEY (candidat_id) REFERENCES candidat (id)');
        $this->addSql('ALTER TABLE document_type_document ADD CONSTRAINT FK_32390901C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_type_document ADD CONSTRAINT FK_323909018826AFA6 FOREIGN KEY (type_document_id) REFERENCES type_document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lien ADD CONSTRAINT FK_A532B4B58D0EB82 FOREIGN KEY (candidat_id) REFERENCES candidat (id)');
        $this->addSql('ALTER TABLE lien ADD CONSTRAINT FK_A532B4B5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE type_document_type_candidat ADD CONSTRAINT FK_5125A7338826AFA6 FOREIGN KEY (type_document_id) REFERENCES type_document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_document_type_candidat ADD CONSTRAINT FK_5125A73343C93C13 FOREIGN KEY (type_candidat_id) REFERENCES type_candidat (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidat DROP FOREIGN KEY FK_6AB5B47143C93C13');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A768D0EB82');
        $this->addSql('ALTER TABLE document_type_document DROP FOREIGN KEY FK_32390901C33F7837');
        $this->addSql('ALTER TABLE document_type_document DROP FOREIGN KEY FK_323909018826AFA6');
        $this->addSql('ALTER TABLE lien DROP FOREIGN KEY FK_A532B4B58D0EB82');
        $this->addSql('ALTER TABLE lien DROP FOREIGN KEY FK_A532B4B5FB88E14F');
        $this->addSql('ALTER TABLE type_document_type_candidat DROP FOREIGN KEY FK_5125A7338826AFA6');
        $this->addSql('ALTER TABLE type_document_type_candidat DROP FOREIGN KEY FK_5125A73343C93C13');
        $this->addSql('DROP TABLE candidat');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_type_document');
        $this->addSql('DROP TABLE lien');
        $this->addSql('DROP TABLE type_candidat');
        $this->addSql('DROP TABLE type_document');
        $this->addSql('DROP TABLE type_document_type_candidat');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
