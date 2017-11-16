<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "log_action".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Log[] $logs
 */
class LogAction extends \yii\db\ActiveRecord
{
    /** Добавление объекта в дерево */
    const NODE_ADD = 1;
    /** Редактирование объекта дерева */
    const NODE_EDIT = 2;
    /** Удаление объекта дерева */
    const NODE_DELETE = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_action';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::className(), ['action_id' => 'id']);
    }
}
