<?php

use yii\db\Migration;

/**
 * Handles the creation of table 'users'.
 */
class m210519_163258_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'password' => $this->string()->notNull(),
            'auth_key' => $this->string(32),
            'access_token' => $this->string(100),
            'password_reset_token' => $this->string(100),
            'email' => $this->string()->notNull()->unique(),
            'role' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->string(100),
            'updated_at' => $this->string(100),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
