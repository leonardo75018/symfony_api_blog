<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503121646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, state TINYINT(1) NOT NULL, parution DATETIME NOT NULL, INDEX IDX_23A0E66F675F31B (author_id), INDEX IDX_23A0E6612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, article_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, state TINYINT(1) NOT NULL, clear VARCHAR(255) NOT NULL, INDEX IDX_F86E9D2F675F31B (author_id), INDEX IDX_F86E9D27294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE coment ADD CONSTRAINT FK_F86E9D2F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE coment ADD CONSTRAINT FK_F86E9D27294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('DROP TABLE product');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(100) NOT NULL, ADD last_name VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE coment DROP FOREIGN KEY FK_F86E9D2F675F31B');
        $this->addSql('ALTER TABLE coment DROP FOREIGN KEY FK_F86E9D27294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE coment');
        $this->addSql('ALTER TABLE `user` DROP first_name, DROP last_name');
    }
}
