<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260821111924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove UNIQUE index on tags_to_blog.tag_id for proper ManyToMany';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tags_to_blog DROP FOREIGN KEY FK_147AB9DBAD26311');
        $this->addSql('DROP INDEX UNIQ_147AB9DBAD26311 ON tags_to_blog');
        $this->addSql('CREATE INDEX IDX_147AB9DBAD26311 ON tags_to_blog (tag_id)');
        $this->addSql('ALTER TABLE tags_to_blog ADD CONSTRAINT FK_147AB9DBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tags_to_blog DROP FOREIGN KEY FK_147AB9DBAD26311');
        $this->addSql('DROP INDEX IDX_147AB9DBAD26311 ON tags_to_blog');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_147AB9DBAD26311 ON tags_to_blog (tag_id)');
        $this->addSql('ALTER TABLE tags_to_blog ADD CONSTRAINT FK_147AB9DBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
    }
}
