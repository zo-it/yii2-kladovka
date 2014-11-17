<?php

namespace yii\kladovka\console;

use yii\console\Controller,
    yii\helpers\Json,
    yii\kladovka\helpers\Log,
    yii\helpers\Inflector,
    yii\helpers\StringHelper,
    Yii;


class GenerateController extends Controller
{

    public $filename = './generate.json';

    public $dirMode = '0777';

    public $overwriteAll = false;

    public function init()
    {
        if (strncmp($this->filename, './', 2) == 0) {
            $this->filename = Yii::$app->getBasePath() . substr($this->filename, 1);
        }
        parent::init();
    }

    public function options($actionId)
    {
        return array_merge(parent::options($actionId), ['filename', 'dirMode', 'overwriteAll']);
    }

    protected $_commands = [];

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (is_file($this->filename)) {
                $this->_commands = Json::decode(file_get_contents($this->filename));
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        file_put_contents($this->filename, Json::encode($this->_commands, \JSON_PRETTY_PRINT));
        return $result;
    }

    public function actionDbSchema()
    {
        Log::beginMethod(__METHOD__);
        $sqlPath = Yii::getAlias('@app/sql');
        if (!is_dir($sqlPath)) {
            mkdir($sqlPath, octdec($this->dirMode));
        }
        $db = Yii::$app->getDb();
        parse_str(str_replace(';', '&', substr($db->dsn, 6)), $dsnParams);
        $filename = $sqlPath . DIRECTORY_SEPARATOR . $dsnParams['dbname'] . '-schema.sql';
        $command = 'mysqldump --create-options --no-data --events' .
            ' --host=' . escapeshellarg(array_key_exists('host', $dsnParams) ? $dsnParams['host'] : 'localhost') .
            ' --user=' . escapeshellarg($db->username) .
            ' --password=' . escapeshellarg($db->password) .
            ' ' . escapeshellarg($dsnParams['dbname']) .
            ' | sed -e ' . escapeshellarg('s/ AUTO_INCREMENT=[0-9]\+//') .
            ' > ' . escapeshellarg($filename);
        passthru($command);
        Log::endMethod(__METHOD__);
    }

    public function actionBaseModels()
    {
        Log::beginMethod(__METHOD__);
        $readonlyPath = Yii::getAlias('@app/models/readonly');
        if (!is_dir($readonlyPath)) {
            mkdir($readonlyPath, octdec($this->dirMode));
        }
        $baseClass = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveRecord' : 'yii\kladovka\db\ActiveRecord';
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'Base';
            $args = [
                'gii/model',
                'tableName' => $tableName,
                'ns' => $ns,
                'modelClass' => StringHelper::basename($modelClass),
                'baseClass' => $baseClass,
                'generateLabelsFromComments' => 1,
                'interactive' => 0,
                'overwrite' => 1
            ];
            if (array_key_exists($modelClass, $this->_commands)) {
                $this->_commands[$modelClass] += $args;
            } else {
                $this->_commands[$modelClass] = $args;
            }
        }
        Log::endMethod(__METHOD__);
    }

    public function actionModels()
    {
        Log::beginMethod(__METHOD__);
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'Base';
            $secondModelClass = $ns . '\\' . $modelName;
            $args = [
                'gii/model2',
                'modelClass' => $modelClass,
                'secondModelClass' => $secondModelClass,
                'interactive' => 0,
                'overwrite' => 0
            ];
            if (array_key_exists($secondModelClass, $this->_commands)) {
                $this->_commands[$secondModelClass] += $args;
            } else {
                $this->_commands[$secondModelClass] = $args;
            }
        }
        Log::endMethod(__METHOD__);
    }

    public function actionBaseSearchModels()
    {
        Log::beginMethod(__METHOD__);
        $searchPath = Yii::getAlias('@app/models/search');
        if (!is_dir($searchPath)) {
            mkdir($searchPath, octdec($this->dirMode));
        }
        $readonlySearchPath = Yii::getAlias('@app/models/readonly/search');
        if (!is_dir($readonlySearchPath)) {
            mkdir($readonlySearchPath, octdec($this->dirMode));
        }
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName;
            $searchModelClass = $ns . '\search\\' . $modelName . 'SearchBase';
            $args = [
                'gii/search',
                'modelClass' => $modelClass,
                'searchModelClass' => $searchModelClass,
                'interactive' => 0,
                'overwrite' => 1
            ];
            if (array_key_exists($searchModelClass, $this->_commands)) {
                $this->_commands[$searchModelClass] += $args;
            } else {
                $this->_commands[$searchModelClass] = $args;
            }
        }
        Log::endMethod(__METHOD__);
    }

    public function actionSearchModels()
    {
        Log::beginMethod(__METHOD__);
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly\search' : 'app\models\search';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'SearchBase';
            $secondModelClass = $ns . '\\' . $modelName . 'Search';
            $args = [
                'gii/search2',
                'modelClass' => $modelClass,
                'secondModelClass' => $secondModelClass,
                'interactive' => 0,
                'overwrite' => 1
            ];
            if (array_key_exists($secondModelClass, $this->_commands)) {
                $this->_commands[$secondModelClass] += $args;
            } else {
                $this->_commands[$secondModelClass] = $args;
            }
        }
        Log::endMethod(__METHOD__);
    }

    public function actionControllers()
    {
        Log::beginMethod(__METHOD__);
        $baseControllerClass = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\crud\ActiveController' : 'yii\web\Controller';
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName;
            $controllerClass = 'app\controllers\\' . $modelName . 'Controller';
            $args = [
                'gii/controller2',
                'modelClass' => $modelClass,
                'controllerClass' => $controllerClass,
                'baseControllerClass' => $baseControllerClass,
                'interactive' => 0,
                'overwrite' => 0
            ];
            if (array_key_exists($controllerClass, $this->_commands)) {
                $this->_commands[$controllerClass] += $args;
            } else {
                $this->_commands[$controllerClass] = $args;
            }
        }
        Log::endMethod(__METHOD__);
    }

    public function actionMakeAll()
    {
        $this->actionDbSchema();
        $this->actionBaseModels();
        $this->actionModels();
        $this->actionBaseSearchModels();
        $this->actionSearchModels();
        //$this->actionControllers();
    }

    public function actionIndex()
    {
        passthru(Yii::$app->getBasePath() . '/yii help ' . $this->id);
    }
}
