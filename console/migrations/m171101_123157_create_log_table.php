<?php

use yii\db\Migration;
use common\models\LogAction;

/**
 * Handles the creation of table `log`.
 */
class m171101_123157_create_log_table extends Migration
{
    const TABLE_NAME = 'log';
    const TABLE_ACTION_NAME = 'log_action';
    const TABLE_USER_NAME = 'user';
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable(self::TABLE_ACTION_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(256)->notNull(),
        ]);
        (new LogAction(['name' => 'Добавление объекта в дерево']))->save();
        (new LogAction(['name' => 'Редактирование объекта дерева']))->save();
        (new LogAction(['name' => 'Удаление объекта дерева']))->save();
        
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'action_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'old_value' => $this->text()->null(),
            'new_value' => $this->text()->null(),
            'comment' => $this->text()->null(),
            
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'user_ip' => $this->string(16)->notNull(),
        ]);
        $this->createIndex('ind_log_f_action_id', self::TABLE_NAME, 'action_id');
        $this->createIndex('ind_log_f_user_id', self::TABLE_NAME, 'user_id');
        
        $this->addForeignKey('fk_log_f_action_id', self::TABLE_NAME, 'action_id', self::TABLE_ACTION_NAME, 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_log_f_user_id', self::TABLE_NAME, 'user_id', self::TABLE_USER_NAME, 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(self::TABLE_NAME);
        $this->dropTable(self::TABLE_ACTION_NAME);
    }
}
