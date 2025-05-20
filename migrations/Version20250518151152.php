<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518151152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post (id SERIAL NOT NULL, author_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt TEXT DEFAULT NULL, content TEXT NOT NULL, featured_image VARCHAR(255) DEFAULT NULL, is_deleted BOOLEAN NOT NULL, grid_rows SMALLINT NOT NULL, grid_cols SMALLINT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_BA5AE01D989D9B62 ON blog_post (slug)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BA5AE01DF675F31B ON blog_post (author_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN blog_post.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN blog_post.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_post_tag (post_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(post_id, tag_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2E931ED74B89032C ON blog_post_tag (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2E931ED7BAD26311 ON blog_post_tag (tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_tag (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6EC39895E237E06 ON blog_tag (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6EC3989989D9B62 ON blog_tag (slug)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_tag ADD CONSTRAINT FK_2E931ED74B89032C FOREIGN KEY (post_id) REFERENCES blog_post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_tag ADD CONSTRAINT FK_2E931ED7BAD26311 FOREIGN KEY (tag_id) REFERENCES blog_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP CONSTRAINT FK_BA5AE01DF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_tag DROP CONSTRAINT FK_2E931ED74B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post_tag DROP CONSTRAINT FK_2E931ED7BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_post_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE blog_tag
        SQL);
    }
}
