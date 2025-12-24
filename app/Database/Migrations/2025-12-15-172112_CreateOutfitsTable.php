<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOutfitsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'outfit_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ai_generated' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('outfit_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_id', 'events', 'event_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('outfits');
    }

    public function down()
    {
        $this->forge->dropTable('outfits');
    }
}