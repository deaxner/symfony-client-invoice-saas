<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add commercial projects and link invoices to projects.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(100) NOT NULL, billing_model VARCHAR(20) NOT NULL, hourly_rate NUMERIC(10, 2) DEFAULT NULL, internal_cost_rate_default NUMERIC(10, 2) DEFAULT NULL, sla_monthly_fee NUMERIC(10, 2) DEFAULT NULL, monthly_hours_included INT DEFAULT NULL, fixed_monthly_retainer NUMERIC(10, 2) DEFAULT NULL, active_from DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', active_until DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', is_active TINYINT(1) NOT NULL DEFAULT 1, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_2FB3D0EE77153098 (code), INDEX IDX_2FB3D0EE7E3C61F9 (owner_id), INDEX IDX_2FB3D0EE19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_90651744166D1F9C ON invoice (project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744166D1F9C');
        $this->addSql('DROP INDEX IDX_90651744166D1F9C ON invoice');
        $this->addSql('ALTER TABLE invoice DROP project_id');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE7E3C61F9');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE19EB6921');
        $this->addSql('DROP TABLE project');
    }
}
