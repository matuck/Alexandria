<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130831132927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE download (id BIGINT AUTO_INCREMENT NOT NULL, book_id BIGINT DEFAULT NULL, ip VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_781A827016A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE tagging (id INT AUTO_INCREMENT NOT NULL, tag_id INT DEFAULT NULL, resource_type VARCHAR(50) NOT NULL, resource_id VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A4AED123BAD26311 (tag_id), UNIQUE INDEX tagging_idx (tag_id, resource_type, resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE rating (id BIGINT AUTO_INCREMENT NOT NULL, bookid BIGINT DEFAULT NULL, iphash VARCHAR(255) NOT NULL, rating TINYINT(1) NOT NULL, INDEX IDX_D889262236BB5955 (bookid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE book (id BIGINT AUTO_INCREMENT NOT NULL, author_id BIGINT DEFAULT NULL, serie_id BIGINT DEFAULT NULL, featured_id BIGINT DEFAULT NULL, isbn VARCHAR(20) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, summary LONGTEXT DEFAULT NULL, serie_nbr BIGINT DEFAULT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, rated BIGINT DEFAULT NULL, INDEX IDX_CBE5A331F675F31B (author_id), INDEX IDX_CBE5A331D94388BD (serie_id), UNIQUE INDEX UNIQ_CBE5A331306FF23 (featured_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE authorvotes (id BIGINT AUTO_INCREMENT NOT NULL, authorid BIGINT DEFAULT NULL, iphash VARCHAR(255) NOT NULL, INDEX IDX_E841820B3412DD5F (authorid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE serie (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE flags (id BIGINT AUTO_INCREMENT NOT NULL, book_id BIGINT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, complete TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B0541BA16A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE chillingdmca (id BIGINT AUTO_INCREMENT NOT NULL, book_title VARCHAR(255) DEFAULT NULL, book_author VARCHAR(255) DEFAULT NULL, dmca_name VARCHAR(255) DEFAULT NULL, dmca_email VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), UNIQUE INDEX UNIQ_389B783989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE featured (id BIGINT AUTO_INCREMENT NOT NULL, book_id BIGINT DEFAULT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3C1359D416A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE author (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, facebook VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, biography LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE download ADD CONSTRAINT FK_781A827016A2B381 FOREIGN KEY (book_id) REFERENCES book (id)");
        $this->addSql("ALTER TABLE tagging ADD CONSTRAINT FK_A4AED123BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)");
        $this->addSql("ALTER TABLE rating ADD CONSTRAINT FK_D889262236BB5955 FOREIGN KEY (bookid) REFERENCES book (id)");
        $this->addSql("ALTER TABLE book ADD CONSTRAINT FK_CBE5A331F675F31B FOREIGN KEY (author_id) REFERENCES author (id)");
        $this->addSql("ALTER TABLE book ADD CONSTRAINT FK_CBE5A331D94388BD FOREIGN KEY (serie_id) REFERENCES serie (id)");
        $this->addSql("ALTER TABLE book ADD CONSTRAINT FK_CBE5A331306FF23 FOREIGN KEY (featured_id) REFERENCES featured (id)");
        $this->addSql("ALTER TABLE authorvotes ADD CONSTRAINT FK_E841820B3412DD5F FOREIGN KEY (authorid) REFERENCES author (id)");
        $this->addSql("ALTER TABLE flags ADD CONSTRAINT FK_B0541BA16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)");
        $this->addSql("ALTER TABLE featured ADD CONSTRAINT FK_3C1359D416A2B381 FOREIGN KEY (book_id) REFERENCES book (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE download DROP FOREIGN KEY FK_781A827016A2B381");
        $this->addSql("ALTER TABLE rating DROP FOREIGN KEY FK_D889262236BB5955");
        $this->addSql("ALTER TABLE flags DROP FOREIGN KEY FK_B0541BA16A2B381");
        $this->addSql("ALTER TABLE featured DROP FOREIGN KEY FK_3C1359D416A2B381");
        $this->addSql("ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331D94388BD");
        $this->addSql("ALTER TABLE tagging DROP FOREIGN KEY FK_A4AED123BAD26311");
        $this->addSql("ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331306FF23");
        $this->addSql("ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331F675F31B");
        $this->addSql("ALTER TABLE authorvotes DROP FOREIGN KEY FK_E841820B3412DD5F");
        $this->addSql("DROP TABLE download");
        $this->addSql("DROP TABLE tagging");
        $this->addSql("DROP TABLE rating");
        $this->addSql("DROP TABLE book");
        $this->addSql("DROP TABLE authorvotes");
        $this->addSql("DROP TABLE serie");
        $this->addSql("DROP TABLE flags");
        $this->addSql("DROP TABLE chillingdmca");
        $this->addSql("DROP TABLE tag");
        $this->addSql("DROP TABLE featured");
        $this->addSql("DROP TABLE users");
        $this->addSql("DROP TABLE author");
    }
}
