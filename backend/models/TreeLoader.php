<?php

namespace backend\models;

use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use common\models\ImportMaster;
use common\models\DictOkpd2;

/**
 * Класс для загрузки справочника ОКПД2
 */
class TreeLoader extends Model
{
    /** @var UploadedFile|Null file attribute */
    var $file;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'extensions' => 'xlsx', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => false, 'uploadRequired' => 'Пожалуйста, выберите файл.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => 'Файл для загрузки'
        ];
    }

    public function loadTree()
    {
        $path = Yii::getAlias('@temp') . DIRECTORY_SEPARATOR . $this->file->baseName . '.' . $this->file->extension;
        $this->file->saveAs($path);
        $this->parseTree($path);
        unlink($path);
    }
    
    /**
     * Парсим xlsx файл и сохраняем дерево. Используем итеративный обход рекурсивной
     * структуры ради экономии ресурсов 
     * @param  string $fileName Имя импортируемого файла
     */
    private function parseTree($fileName) {
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
    
}
