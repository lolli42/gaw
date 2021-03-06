<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20131209173821 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		// this up() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE lolli_gaw_domain_model_planetstructurebuildqueueitem (persistence_object_identifier VARCHAR(40) NOT NULL, planet VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, readytime BIGINT UNSIGNED NOT NULL, INDEX IDX_CCA38D8F68136AA5 (planet), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("ALTER TABLE lolli_gaw_domain_model_planetstructurebuildqueueitem ADD CONSTRAINT FK_CCA38D8F68136AA5 FOREIGN KEY (planet) REFERENCES lolli_gaw_domain_model_planet (persistence_object_identifier)");
		$this->addSql("ALTER TABLE lolli_gaw_domain_model_planet DROP structureinprogress, DROP structurereadytime");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		// this down() migration is autogenerated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE lolli_gaw_domain_model_planetstructurebuildqueueitem");
		$this->addSql("ALTER TABLE lolli_gaw_domain_model_planet ADD structureinprogress INT NOT NULL, ADD structurereadytime BIGINT UNSIGNED NOT NULL");
	}
}