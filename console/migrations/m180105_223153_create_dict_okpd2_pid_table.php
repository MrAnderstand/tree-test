<?php

use yii\db\Migration;

/**
 * Handles the creation of table `dict_okpd2_pid`.
 */
class m180105_223153_create_dict_okpd2_pid_table extends Migration
{
    public $table_dict_okpd2_pid = 'dict_okpd2_pid';
    
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable($this->table_dict_okpd2_pid, [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->null(),
            'code' => $this->string(32),
            'name' => $this->text(),
        ]);
        $this->createIndex('idx-' . $this->table_dict_okpd2_pid . '-parent_id', $this->table_dict_okpd2_pid, 'parent_id');
        
        $this->addForeignKey(
            'fk-' . $this->table_dict_okpd2_pid . '-parent_id',
            $this->table_dict_okpd2_pid,
            'parent_id',
            $this->table_dict_okpd2_pid,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable($this->table_dict_okpd2_pid);
    }
}
