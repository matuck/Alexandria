<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130904151927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE dailydownloads (id BIGINT AUTO_INCREMENT NOT NULL, day DATETIME NOT NULL, downloads BIGINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE book ADD downcount BIGINT DEFAULT NULL");
        $this->addSql("INSERT INTO dailydownloads (day, downloads) SELECT DATE(created_at) date, count(*) FROM download GROUP BY DATE(created_at)");
        $this->addSql('UPDATE book SET book.downcount = IFNULL(book.downcount, 0) + ( SELECT COUNT( d.book_id ) FROM download d WHERE d.book_id = book.id )');
        $this->addSql('DROP TABLE download');
        $this->addSql("CREATE TABLE download (id BIGINT AUTO_INCREMENT NOT NULL, book_id BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_781A827016A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE download ADD CONSTRAINT FK_781A827016A2B381 FOREIGN KEY (book_id) REFERENCES book (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE dailydownloads");
        $this->addSql("ALTER TABLE book DROP downcount");
        $this->addSql("ALTER TABLE download DROP FOREIGN KEY FK_781A827016A2B381");
        $this->addSql("ALTER TABLE download ADD ip VARCHAR(20) DEFAULT NULL");
    }
}
