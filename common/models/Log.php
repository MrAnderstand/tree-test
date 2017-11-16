<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "log".
 *
 * @property integer $id
 * @property integer $action_id
 * @property integer $user_id
 * @property string $old_value
 * @property string $new_value
 * @property string $comment
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $user_ip
 *
 * @property LogAction $action
 * @property User $user
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action_id', 'user_id', 'user_ip'], 'required'],
            [['action_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['old_value', 'new_value', 'comment'], 'string'],
            [['user_ip'], 'string', 'max' => 16],
            [['action_id'], 'exist', 'skipOnError' => true, 'targetClass' => LogAction::className(), 'targetAttribute' => ['action_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'action_id' => 'Action ID',
            'user_id' => 'User ID',
            'old_value' => 'Old Value',
            'new_value' => 'New Value',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'user_ip' => 'User Ip',
        ];
    }
    
    /**
     * Логгирует событие
     * @param  int      $actionId   Совершаемое действие
     * @param  string   $oldValue   Старое значение
     * @param  string   $newValue   Новое значение
     * @param  string   $comment    Комментарий к действию
     * @return boolean              Результат выполнения
     */
    public static function write($actionId, $oldValue, $newValue, $comment = '')
    {
        return (new Log([
            'action_id' => $actionId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
            'user_id' => Yii::$app->user->identity->id,
            'user_ip' => Yii::$app->request->userIP,
        ]))->save();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAction()
    {
        return $this->hasOne(LogAction::className(), ['id' => 'action_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
