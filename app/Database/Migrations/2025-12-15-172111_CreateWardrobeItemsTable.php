<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWardrobeItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'item_id' => [
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
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'style' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'image_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('item_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'category_id');
        $this->forge->createTable('wardrobe_items');
    }

    public function down()
    {
        $this->forge->dropTable('wardrobe_items');
    }
}