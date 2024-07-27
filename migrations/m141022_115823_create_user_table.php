<?php
// Created By @hoaaah * Copyright (c) 2020 belajararief.com

use yii\db\Migration;


class m141022_115823_create_user_table extends Migration
{
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_given' => $this->smallInteger()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'account_activation_token' => $this->string()->unique(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->insert('user', [
            'username' => 'admin',
            'email' => 'admin@email.com',
            'password_hash' => '$2y$10$s8DF28RMM2Rt34GqAcchNOq.rm7AnHvQxI7hZGyFS2D0VmSalx54O',
            'status' => 10,
            'auth_key' => 'pOrVSDE3PGfJcyhPTHWRriu0lHPEtCqJ',
            'access_given' => 99,
            'password_reset_token' => null,
            'account_activation_token' => null,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
