<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260410140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create character and game tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `character` (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            `class` VARCHAR(50) NOT NULL,
            race VARCHAR(50) NOT NULL,
            strength INT NOT NULL DEFAULT 10,
            dexterity INT NOT NULL DEFAULT 10,
            constitution INT NOT NULL DEFAULT 10,
            intelligence INT NOT NULL DEFAULT 10,
            wisdom INT NOT NULL DEFAULT 10,
            charisma INT NOT NULL DEFAULT 10,
            INDEX IDX_CHARACTER_USER (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE game (
            id INT AUTO_INCREMENT NOT NULL,
            character_id INT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT \'active\',
            history JSON NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_GAME_CHARACTER (character_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_CHARACTER_USER FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_GAME_CHARACTER FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_GAME_CHARACTER');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_CHARACTER_USER');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE `character`');
    }
}
