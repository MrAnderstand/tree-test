<?php

namespace backend\models;

use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use common\models\ImportMaster;
use common\models\DictOkpd2;
use common\models\DictOkpd2Pid;

/**
 * Класс для загрузки справочника ОКПД2
 */
class TreeLoader extends Model
{
    /** Дерево с реализацией с помощью nested sets */
    public const TREE_TYPE_NESTED_SETS = 1;
    /** Дерево с реализацией с помощью parent_id */
    public const TREE_TYPE_PID = 2;
    
    /** @var UploadedFile|Null file attribute */
    public $file;
    /** @var int Тип загружаемого дерева */
    public $tree_type;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'extensions' => 'xlsx', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => false, 'uploadRequired' => 'Пожалуйста, выберите файл.'],
            [['tree_type'], 'required'],
            ['tree_type', 'in', 'range' => [self::TREE_TYPE_NESTED_SETS, self::TREE_TYPE_PID]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => 'Файл для загрузки',
            'tree_type' => 'Тип дерева',
        ];
    }

    public function loadTree()
    {
        $path = Yii::getAlias('@temp') . DIRECTORY_SEPARATOR . $this->file->baseName . '.' . $this->file->extension;
        $this->file->saveAs($path);
        switch ($this->tree_type) {
            case self::TREE_TYPE_NESTED_SETS:
                $this->parseTreeNestedSets($path);
                break;
            case self::TREE_TYPE_PID:
                $this->parseTreePid($path);
                DictOkpd2Pid::clearCache();
                break;
        }
        unlink($path);
    }
    
    /**
     * Парсим xlsx файл и сохраняем дерево. Используем итеративный обход рекурсивной
     * структуры ради экономии ресурсов 
     * @param  string $fileName Имя импортируемого файла
     */
    private function parseTreeNestedSets($fileName) {
        set_time_limit(2 * 60);
        $importer = new ImportMaster($fileName);
        $dataxls = $importer->parse();
        
        $connection = \Yii::$app->db;
        $connection->enableLogging = false;
        $connection->enableProfiling = false;
        $transaction = $connection->beginTransaction();
        // Перед загрузкой все удалим
        DictOkpd2::deleteAll();
        
        $mass = [];
        try {
            $r = 0;
            $N = count($dataxls);
            $parent = null;
            while ($r < $N) {
                $code = trim($dataxls[$r][0]);
                $result = false;
                $params = [];
                $params['code'] = $code;
                $params['name'] = trim($dataxls[$r][1]);
                if ($parent) {
                    // Если текущий элемент не корень дерева и предок корень или является родителем текущего, то сохраним потомка
                    // иначе вернемся к родителю, чтобы проверить, является ли текущий элемент его поддеревом
                    if ($code != '' and ($parent->code == '' or strpos($code, $parent->code) !== false)) {
                        $node = new DictOkpd2($params);
                        $result = $node->appendTo($parent);
                    } else {
                        $parent = $parent->parents(1)->one();
                        continue;
                    }
                } else {
                    $node = new DictOkpd2($params);
                    $result = $node->makeRoot();
                }
                if (!$result) {
                    // throw new ErrorException("Ошибка при сохранении ОКПД2. Проверьте структуру файла <b>в районе строки " . ($r + 1) . "</b> и повторите операцию или обратитесь в поддержку.");
                    var_dump([$node->errors, $params]);die;
                }
                $r++;
                $parent = $node;
            }
            Yii::$app->getSession()->setFlash('success', $msg = 'Данные успешно загружены.');
            $transaction->commit();
        } catch (ErrorException $e) {
            switch ($e->getCode()) {
                default:
                    Yii::$app->getSession()->setFlash('error', $msg = $e->getMessage());
            }
            $transaction->rollBack();
        }
    }
    
    /**
     * Парсим xlsx файл и сохраняем дерево. Используем итеративный обход рекурсивной
     * структуры ради экономии ресурсов 
     * @param  string $fileName Имя импортируемого файла
     */
    private function parseTreePid($fileName) {
        // set_time_limit(6 * 60);
        $importer = new ImportMaster($fileName);
        $dataxls = $importer->parse();
        
        \Yii::$app->db->enableLogging = false;
        \Yii::$app->db->enableProfiling = false;
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        // Перед загрузкой все удалим
        DictOkpd2Pid::deleteAll();
        
        $mass = [];
        try {
            $r = 0;
            $N = count($dataxls);
            $parent = null;
            while ($r < $N) {
                $code = trim($dataxls[$r][0]);
                $params = [];
                if ($parent) {
                    // Если текущий элемент не корень дерева и предок корень или является родителем текущего, то сохраним потомка
                    // иначе вернемся к родителю, чтобы проверить, является ли текущий элемент его поддеревом
                    if ($code != '' and ($parent->code == '' or strpos($code, $parent->code) !== false)) {
                        $params['parent_id'] = $parent->id;
                    } else {
                        $parent = $parent->parent;
                        continue;
                    }
                }
                $params['code'] = $code;
                $params['name'] = trim($dataxls[$r][1]);
                $DO2 = new DictOkpd2Pid($params);
                if (!$DO2->save()) {
                    throw new ErrorException("Ошибка при сохранении ОКПД2. Проверьте структуру файла <b>в районе строки " . ($r + 1) . "</b> и повторите операцию или обратитесь в поддержку.");
                    // var_dump([$DO2->errors, $params]);die;
                }
                $r++;
                $parent = $DO2;
            }
            Yii::$app->getSession()->setFlash('success', $msg = 'Данные успешно загружены.');
            $transaction->commit();
        } catch (ErrorException $e) {
            switch ($e->getCode()) {
                default:
                    Yii::$app->getSession()->setFlash('error', $msg = $e->getMessage());
            }
            $transaction->rollBack();
        }
    }
}
