<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add fraisDossierDefaut and pourcentageAcompteDefaut Parameter to available parameters.
 */
class Version20171123202155 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        /*
         * Jeyser got migrations quite late in the project.
         * This check is to keep thing working smoothly on every install: migration is not performed is its result
         * is already there.
         * fetch() equals to an array if the column exist, and false otherwise.
         */
        $this->skipIf(is_array($this->connection->executeQuery('SELECT * FROM AdminParam where name = "fraisDossierDefaut"')
                ->fetch()) &&
            is_array($this->connection->executeQuery('SELECT * FROM AdminParam where name = "fraisDossierDefaut"')
                ->fetch()), 'fraisDossierDefaut & pourcentageAcompteDefaut already in AdminParam table');

        $this->addSql('INSERT INTO `AdminParam` (`id`, `name`, `paramType`, `defaultValue`, `required`, `paramLabel`, 
`paramDescription`, `priority`) VALUES (NULL, \'fraisDossierDefaut\', \'string\', \'90\', \'1\', 
\'Frais de dossier par défaut\', \'Valeur par défaut des frais de dossier à la création de l Avant-Projet\', \'810\')');
        $this->addSql('INSERT INTO `AdminParam` (`id`, `name`, `paramType`, `defaultValue`, `required`, `paramLabel`, 
`paramDescription`, `priority`) VALUES (NULL, \'pourcentageAcompteDefaut\', \'number\', \'0.4\', \'1\', 
\'Acompte par défaut\', \'Valeur par défaut de l acompte à la création de la Convention Client\', \'800\')');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('DELETE from AdminParam where name = "fraisDossierDefaut"');
        $this->addSql('DELETE from AdminParam where name = "pourcentageAcompteDefaut"');
    }
}
