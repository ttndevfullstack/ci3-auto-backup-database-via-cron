<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_backup_tables extends CI_Migration {
    public function up() {
        // Backup Settings Table
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'backup_time' => ['type' => 'TIME', 'null' => FALSE],
            'max_local_backups' => ['type' => 'INT', 'constraint' => 11, 'default' => 5],
            'enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE],
            'updated_at' => ['type' => 'DATETIME', 'null' => FALSE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('backup_settings');

        // Backup History Table
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'size' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            's3_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('backup_history');
    }

    public function down() {
        $this->dbforge->drop_table('backup_settings');
        $this->dbforge->drop_table('backup_history');
    }
}