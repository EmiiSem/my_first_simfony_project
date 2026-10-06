<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005130925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create comment table and align timestamp columns';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['comment'])) {
            $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, blog_id INT NOT NULL, INDEX IDX_9474526CDAE07E97 (blog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
            $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CDAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id)');
        }

        $this->addSql('ALTER TABLE tag CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($schemaManager->tablesExist(['comment'])) {
            $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CDAE07E97');
            $this->addSql('DROP TABLE comment');
        }

        $this->addSql('ALTER TABLE tag CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
