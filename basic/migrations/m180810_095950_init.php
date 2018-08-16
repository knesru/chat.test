<?php

use yii\db\Migration;
use yii\db\mysql\Schema;

/**
 * Class m180810_095950_init
 */
class m180810_095950_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_PK,
            'nickname' => Schema::TYPE_STRING . ' UNIQUE NOT NULL',
            'auth_key' => Schema::TYPE_STRING . '(32)',
            'access_token_hash' => Schema::TYPE_STRING . '(50)',
            'password_hash' => Schema::TYPE_STRING . '(50)',//sha1+salt
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING ,
            'type' => Schema::TYPE_STRING . '(10) NOT NULL default "user"',//user|admin|moderator|group
            'group_id'=>Schema::TYPE_INTEGER,
            'status'=>Schema::TYPE_STRING,
            // new,
            // verified - normal,
            // devoice - can write messages, but nobody see them,
            // ban - cannot write messages or read,
            // block - cannot even login :)
            'status_reason'=>Schema::TYPE_STRING,
            'status_user_id'=>Schema::TYPE_INTEGER,
            'status_date'=>Schema::TYPE_INTEGER,

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        $this->createTable('{{%group_members}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER,
            'group_id' => Schema::TYPE_INTEGER,

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        $this->addForeignKey('FK_group_members_user_user_id', '{{%group_members}}', ['user_id'], '{{%user}}', ['id'], 'CASCADE');
        $this->addForeignKey('FK_group_members_user_user_as_group_id', '{{%group_members}}', ['group_id'], '{{%user}}', ['id'], 'CASCADE');
        $this->addForeignKey('FK_user_user_user_as_group_id', '{{%user}}', ['group_id'], '{{%user}}', ['id'], 'CASCADE');
        $this->addForeignKey('FK_user_user_user_id', '{{%user}}', ['status_user_id'], '{{%user}}', ['id'], 'CASCADE');


        $this->createTable('{{%messages}}', [
            'id' => Schema::TYPE_PK,
            'message' => Schema::TYPE_TEXT,
            'user_id' => Schema::TYPE_INTEGER,
            'to' => Schema::TYPE_INTEGER,

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        $this->addForeignKey('FK_messages_user_user_id', '{{%messages}}', ['user_id'], '{{%user}}', ['id'], 'CASCADE');


        $this->insert('{{%user}}',[
            'id'=>NULL,
            'nickname'=>'all',
            'type'=>'group',
            'group_id'=>NULL,
            'created_at'=>time(),
            'updated_at'=>time()
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_messages_user_user_id','{{%messages}}');
        $this->dropTable('{{%messages}}');
        $this->dropForeignKey('FK_user_user_user_as_group_id', '{{%user}}');
        $this->dropForeignKey('FK_group_members_user_user_id', '{{%group_members}}');
        $this->dropForeignKey('FK_group_members_user_user_as_group_id', '{{%group_members}}');
        $this->dropForeignKey('FK_user_user_user_id', '{{%user}}');
        $this->dropTable('{{%group_members}}');
        $this->dropTable('{{%user}}');
    }
}
