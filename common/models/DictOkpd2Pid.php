<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dict_okpd2_pid".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $code
 * @property string $name
 *
 * @property DictOkpd2Pid $parent
 * @property DictOkpd2Pid[] $nodes
 */
class DictOkpd2Pid extends \yii\db\ActiveRecord
{
    /** Индекс закэшированного дерева */
    private const OKPD2_PID_TREE = 'okpd2_pid_tree';
    /** Индекс закэшированного массива элементов дерева */
    private const OKPD2_PID_NODES = 'okpd2_pid_nodes';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dict_okpd2_pid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['parent_id'], 'integer'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => DictOkpd2Pid::className(), 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'code' => 'Code',
            'name' => 'Name',
        ];
    }
    
    public function getTitle()
    {
        return trim($this->code . ' ' . $this->name);
    }
    
    /**
     * Добавляет дочерний элемент
     * @param  int $parentId    Id родительского элемента
     * @return bool             True при успешном выполнении
     */
    public function appendTo($parentId)
    {
        $this->parent_id = $parentId;
        return $this->save();
    }
    
    /**
     * Возвращает массив всех элементов дерева
     */
    private static function getFullTree()
    {
        $treeData = [];
        $tableName = self::tableName();
        $command = Yii::$app->db->createCommand("SELECT
                parent.id AS 'key',
                CONCAT(parent.code, ' ', parent.name) AS title,
                parent.parent_id AS pid,
                child.id AS cid
            FROM `$tableName` AS parent
            LEFT JOIN `$tableName` child ON child.parent_id = parent.id
        ");
        foreach ($command->query() as $row) {
            $cid = array_pop($row);     // Извлечем cid
            $treeData[$row['key']] = $row;
            if ($cid !== false) {
                $treeData[$row['key']]['isFolder'] = true;
            }
        }
        return $treeData;
    }
    
    /**
     * Строит дерево 
     * @param  string  $sql    SQL запрос к таблице с данными дерева
     * @param  boolean $expand True, если папки в дереве должны быть открытыми
     * @param  string  $search Искомая строка
     * @return array           Дерево в виде иерархического массива
     */
    private static function buildTree($sql, $expand = false, $search = null) {
        $treeData = [];
        $nodes = [];
        // echo $time_start = microtime(true);
        // sleep(1);
        // echo microtime(true) - $time_start; 
        
        // Закэшируем массив всех элементов дерева
        $cache = Yii::$app->cache;
        $fullTreeData = $cache->getOrSet(self::OKPD2_PID_NODES, function () {
            return self::getFullTree();
        }, 10000);
        // echo microtime(true) - $time_start; 
        
        // $searchData = Yii::$app->db->createCommand($sql)->queryAll();

        // echo microtime(true) - $time_start; 
        
        $command = Yii::$app->db->createCommand($sql);

        foreach ($command->query() as $row) {
            $id = $row['key'];
            $pid = $row['pid'];
            if (!empty($search)) {
                $row['title'] = preg_replace("/({$search})/ius", "<b class='hl'>$1</b>", $row['title']);
            }
            $nodes[$id] = $row;
            while ($pid !== null) {
                if (isset($nodes[$pid])) {
                    $nodes[$pid]['children'][] = &$nodes[$id];
                    break;
                } else {
                    $nodes[$pid] = $fullTreeData[$pid];
                    $nodes[$pid]['expand'] = $expand;
                    $nodes[$pid]['children'] = [&$nodes[$id]];
                }
                $id = $pid;
                $pid = $fullTreeData[$id]['pid'];
            }
            if ($pid === null) {
                $treeData[] = &$nodes[$id];
            }
        }
        // Удалим лишнюю информацию
        array_walk($nodes, function (&$row) {
            unset($row['pid']);
        });
        
        // echo microtime(true) - $time_start; 
        // echo '<pre>';
        // var_dump($nodes);
        // echo '</pre>';
        // die;
        return $treeData;
    }

    /**
     * Формирует поисковой запрос к таблице дерева и отдает функции формирования дерева
     * @param  string  $search Искомая строка
     * @param  boolean $expand True, если папки в дереве должны быть открытыми
     * @return array           Дерево в виде иерархического массива
     */
    public static function getSearchTree($search = null, $expand = false) {
        $tableName = self::tableName();
        
        $sql = "SELECT
                parent.id AS 'key',
                CONCAT(parent.code, ' ', parent.name) AS title,
                parent.parent_id AS pid
            FROM `$tableName` AS parent
            LEFT JOIN `$tableName` child ON child.parent_id = parent.id
            WHERE child.id IS NULL 
        ";
        
        if (empty($search)) {
            // Закэшируем полное дерево без фильтрации
            $cache = Yii::$app->cache;
            return $cache->getOrSet(self::OKPD2_PID_TREE, function ($cache) use ($sql, $expand) {
                return self::buildTree($sql, $expand);
            }, 10000);
        } else{
            $sql.= " AND (parent.code LIKE '%$search%' OR parent.name LIKE '%$search%')";
            return self::buildTree($sql, $expand, $search);
        }
    }
    
    /**
     * Функция очистки кэша от данных дерева
     * @return [type] [description]
     */
    public static function clearCache() {
        $cache = Yii::$app->cache;
        $cache->delete(self::OKPD2_PID_TREE);
        $cache->delete(self::OKPD2_PID_NODES);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(DictOkpd2Pid::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNodes()
    {
        return $this->hasMany(DictOkpd2Pid::className(), ['parent_id' => 'id']);
    }

}
