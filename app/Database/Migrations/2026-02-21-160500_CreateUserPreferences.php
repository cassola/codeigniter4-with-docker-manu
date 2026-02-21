<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserPreferences extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('rm_userpreference')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_default_locale' => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'en'],
            'rm_default_site_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_user_id', 'uk_rm_userpreference_user');
        $this->forge->addForeignKey('rm_user_id', 'rm_user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_default_site_id', 'rm_site', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_userpreference', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('rm_userpreference', true);
    }
}
