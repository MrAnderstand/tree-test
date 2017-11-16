<?php

use yii\db\Migration;

/**
 * Handles the creation of table `okpd2`.
 */
class m171023_225445_create_dict_okpd2_table extends Migration
{
    const TABLE_NAME = '{{%dict_okpd2}}';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->bigPrimaryKey(),
            'tree' => $this->integer(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'depth' => $this->smallInteger(5)->notNull(),
            'code' => $this->string(32)->null(),
            'name' => $this->text()->notNull(),
        ], $tableOptions);
        $this->createIndex('dict_okpd2_NK1', self::TABLE_NAME, 'tree');
        $this->createIndex('dict_okpd2_NK2', self::TABLE_NAME, 'lft');
        $this->createIndex('dict_okpd2_NK3', self::TABLE_NAME, 'rgt');
        $this->createIndex('dict_okpd2_NK4', self::TABLE_NAME, 'depth');
    }


    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
