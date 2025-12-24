<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOutfitItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'outfit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('outfit_id', 'outfits', 'outfit_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'wardrobe_items', 'item_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('outfit_items');
    }

    public function down()
    {
        $this->forge->dropTable('outfit_items');
    }
}