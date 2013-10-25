<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131025151603 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE chillingdmca CHANGE book_title book_title VARCHAR(255) NOT NULL, CHANGE book_author book_author VARCHAR(255) NOT NULL, CHANGE dmca_name dmca_name VARCHAR(255) NOT NULL, CHANGE dmca_email dmca_email VARCHAR(255) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE chillingdmca CHANGE book_title book_title VARCHAR(255) DEFAULT NULL, CHANGE book_author book_author VARCHAR(255) DEFAULT NULL, CHANGE dmca_name dmca_name VARCHAR(255) DEFAULT NULL, CHANGE dmca_email dmca_email VARCHAR(255) DEFAULT NULL");
    }
}
