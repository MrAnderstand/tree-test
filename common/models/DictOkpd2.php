<?php

namespace common\models;

use Yii;
use creocoder\nestedsets\NestedSetsBehavior;

/**
 * This is the model class for table "dict_okpd2".
 *
 * @property integer $id
 * @property integer $tree
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property string $code
 * @property string $name
 */
class DictOkpd2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dict_okpd2';
    }
    
    
    public function behaviors() {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                'treeAttribute' => 'tree',
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find()
    {
        return new DictOkpd2Query(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree', 'lft', 'rgt', 'depth'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string'],
            [['code'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tree' => 'Tree',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'depth' => 'Depth',
            'code' => 'Код',
            'name' => 'Наименование',
        ];
    }
    
    public function getTitle()
    {
        return trim($this->code . ' ' . $this->name);
    }
    
    /**
     * Получает данные дерева в виде nested sets. Гораздо более оптимальный вариант
     * чем поиск средствами AR
     * @param  string $search Искомая строка
     */
    private static function getTreeArray($search = null) {
        $tableName = self::tableName();
        if (empty($search)) {
            $treeData = \Yii::$app->db->createCommand("SELECT * FROM `$tableName` ORDER BY `tree`, `lft`")->queryAll();
        } else {
            $treeData = \Yii::$app->db->createCommand("SELECT parent.*
                FROM `$tableName` AS child,
                    `$tableName` AS parent
                WHERE (child.lft BETWEEN parent.lft AND parent.rgt)
                    AND child.rgt - child.lft = 1
                    AND parent.tree = child.tree
                    AND (child.code LIKE '%$search%' OR child.name LIKE '%$search%')
                GROUP BY parent.id
                ORDER BY parent.tree, parent.lft
            ")->queryAll();
        }
        return $treeData;
    }
    
    /**
     * Преобразует nested set дерево к обычному иерархическому массиву, возвращая искомое дерево
     * @param  string  $search     Искомая строка
     * @param  boolean $expand     True, если папки в дереве должны быть открытыми
     */
    public static function getSearchTree($search = null, $expand = false) {
        $stack = [];
        $arraySet = [];
        $treeData = self::getTreeArray($search);
        foreach ($treeData as $intKey => $arrValues) {
            $stackSize = count($stack); //how many opened tags?
            while ($stackSize > 0 and ($stack[$stackSize-1]['rgt'] < $arrValues['lft'] or $arrValues['id'] == $arrValues['tree'])) {
                array_pop($stack); //close sibling and his childrens
                $stackSize--;
            }

            $link = &$arraySet;
            for ($i=0; $i < $stackSize; $i++) {
                $link = &$link[$stack[$i]['index']]["children"]; //navigate to the proper children array
            }
            $tmp = array_push($link, [
                'key' => $arrValues['id'],
                'title' => $arrValues['code'] . ' ' . $arrValues['name'],
                'isFolder' => $arrValues['rgt'] - $arrValues['lft'] > 1,
                'children' => [],
                'expand' => $expand
            ]);
            array_push($stack, [
                'index' => $tmp - 1,
                'rgt' => $arrValues['rgt']
            ]);
        }
        return $arraySet;
    }  
}
