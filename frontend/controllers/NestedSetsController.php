<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use common\models\DictOkpd2;
use common\models\Log;
use common\models\LogAction;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

/**
 * Site controller
 */
class NestedSetsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'search'],
                'rules' => [
                    [
                        'actions' => ['index', 'search'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Отображаем полное дерево
     */
    public function actionIndex()
    {
        Yii::$app->db->enableLogging = false;
        Yii::$app->db->enableProfiling = false;
        
        $treeData = DictOkpd2::getSearchTree();
        $treeData = Json::encode($treeData);
        
        $model = new DictOkpd2();
        
        $controller = $this->id;
        $treeSearchUrl = Url::to(["/$controller/search"]);
        $addSectionUrl = Url::to(["/$controller/add-section"]);
        $addNodeUrl = Url::to(["/$controller/add-node"]);
        $editNodeUrl = Url::to(["/$controller/edit-node"]);
        $deleteNodeUrl = Url::to(["/$controller/delete-node"]);
        
        $modal = $this->renderPartial('_modal', [
            'model' => $model,
            'id' => '',
        ]);
        return $this->render('index', [
            'treeData' => $treeData,
            'modal' => $modal,
            'treeSearchUrl' => $treeSearchUrl,
            'addSectionUrl' => $addSectionUrl,
            'addNodeUrl' => $addNodeUrl,
            'editNodeUrl' => $editNodeUrl,
            'deleteNodeUrl' => $deleteNodeUrl,
        ]);
    }
    
    /**
     * Возвращает Json с деревом, листья которого удовлетворяют результатам поиска
     */
    public function actionSearch()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            
            $search = Yii::$app->request->post('search', '');
            $treeData = DictOkpd2::getSearchTree($search, $search !== '');
            $treeData = Json::encode($treeData);
            return $treeData;
        }
    }
    
    /**
     * Добавляет корень (раздел)
     */
    public function actionAddSection()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            
            $node = new DictOkpd2();
            if ($node->load(Yii::$app->request->post()) and $node->makeRoot()) {
                Log::write(LogAction::NODE_ADD, '', $node->getTitle());
                $res = Json::encode([
                    'action' => 'addSection',
                    'data' => [
                        'key' => $node->id,
                        'title' => $node->getTitle(),
                        'isFolder' => false
                    ]
                ]);
            } else {
                $res = Json::encode(['error' => true]);
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo $res;
            exit;
        }
    }
    
    /**
     * Добавляет дочерний элемент
     */
    public function actionAddNode()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            
            $id = Yii::$app->request->post('id');
            $parent = DictOkpd2::findOne(['id' => $id]);
            $node = new DictOkpd2();
            if ($node->load(Yii::$app->request->post()) and $node->appendTo($parent)) {
                Log::write(LogAction::NODE_ADD, '', $node->getTitle());
                $res = Json::encode([
                    'action' => 'addNode',
                    'data' => [
                        'key' => $node->id,
                        'title' => $node->getTitle(),
                        'isFolder' => false
                    ]
                ]);
            } else {
                $res = Json::encode(['error' => true]);
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo $res;
            exit;
        }
    }
    
    /**
     * Редактирует элемент
     */
    public function actionEditNode()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            
            // var_dump(Yii::$app->request->post());die;
            
            $id = Yii::$app->request->post('id');
            $preload = Yii::$app->request->post('preload');
            $node = DictOkpd2::findOne(['id' => $id]);
            if ($preload) {
                return $this->renderAjax('_modal', [
                    'model' => $node,
                    'id' => $id,
                ]);
            }
            $oldValue = $node->getTitle();
            if ($node->load(Yii::$app->request->post()) and $node->save()) {
                Log::write(LogAction::NODE_EDIT, $oldValue, $node->getTitle());
                $res = Json::encode([
                    'action' => 'editNode',
                    'data' => [
                        'title' => $node->getTitle(),
                    ]
                ]);
            } else {
                $res = Json::encode(['error' => true]);
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo $res;
            exit;
        }
    }
    
    /**
     * Удаляет элемент вместе со всеми дочерними
     */
    public function actionDeleteNode()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            
            $id = Yii::$app->request->post('id');
            $node = DictOkpd2::findOne(['id' => $id]);
            $children = $node->children()->all();
            if ($node->deleteWithChildren()) {
                foreach ($children as $child) {
                    Log::write(LogAction::NODE_DELETE, $child->getTitle(), '', 'Дочерний элемент');
                }
                Log::write(LogAction::NODE_DELETE, $node->getTitle(), '', 'Удалено дочерних элементов: ' . count($children));
                $res = Json::encode([]);
            } else {
                $res = Json::encode(['error' => true]);
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo $res;
            exit;
        }
    }
}
