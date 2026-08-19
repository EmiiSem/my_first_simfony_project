<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260819085333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog (
                            id INT AUTO_INCREMENT NOT NULL,
                            title VARCHAR(255) NOT NULL,
                            desciption VARCHAR(255) NOT NULL,
                            text LONGTEXT NOT NULL,
                            PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE blog');
    }
}
