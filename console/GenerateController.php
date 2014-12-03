<?php

namespace yii\kladovka\console;

use yii\console\Controller,
    yii\helpers\Json,
    yii\helpers\Inflector,
    yii\helpers\StringHelper,
    yii\helpers\Console,
    Yii;


class GenerateController extends Controller
{

    public $filename = './generators.json';

    public $dirMode = '0777';

    public function init()
    {
        if (strncmp($this->filename, './', 2) == 0) {
            $this->filename = Yii::$app->getBasePath() . substr($this->filename, 1);
        }
        parent::init();
    }

    public function options($actionId)
    {
        return array_merge(parent::options($actionId), ['filename', 'dirMode']);
    }

    protected $_savedCommands = [];

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (is_file($this->filename)) {
                $this->_savedCommands = Json::decode(file_get_contents($this->filename));
            }
            return true;
        } else {
            return false;
        }
    }

    protected $_commands = [];

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        foreach ($this->_savedCommands as $targetClass => $args) {
            if (array_key_exists($targetClass, $this->_commands)) {
                $this->_commands[$targetClass] = $args + $this->_commands[$targetClass];
            } else {
                $this->_commands[$targetClass] = $args;
            }
        }
        file_put_contents($this->filename, Json::encode($this->_commands, \JSON_PRETTY_PRINT));
        return $result;
    }

    public function actionBaseModels()
    {
        $readOnlyPath = Yii::getAlias('@app/models/readonly');
        if (!is_dir($readOnlyPath)) {
            mkdir($readOnlyPath, octdec($this->dirMode));
        }
        $hasModuleMozayka = Yii::$app->hasModule('mozayka');
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = 'app\models';
            $baseClass = 'yii\kladovka\db\ActiveRecord';
            if ($tableType == 'VIEW') {
                $ns = 'app\models\readonly';
                if ($hasModuleMozayka) {
                    $baseClass = 'yii\mozayka\db\ReadOnlyActiveRecord';
                }
            } elseif ($hasModuleMozayka) {
                $baseClass = 'yii\mozayka\db\ActiveRecord';
            }
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'Base';
            $this->_commands[$modelClass] = [
                'gii/model',
                'tableName' => $tableName,
                'ns' => $ns,
                'modelClass' => StringHelper::basename($modelClass),
                'baseClass' => $baseClass,
                'generateLabelsFromComments' => 1,
                'interactive' => 0,
                'overwrite' => 1
            ];
        }
    }

    public function actionModels()
    {
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'Base';
            $secondModelClass = $ns . '\\' . $modelName;
            $this->_commands[$secondModelClass] = [
                'gii/model2',
                'modelClass' => $modelClass,
                'secondModelClass' => $secondModelClass,
                'interactive' => 0,
                'overwrite' => 0
            ];
        }
    }

    public function actionBaseSearchModels()
    {
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
            $this->_commands[$searchModelClass] = [
                'gii/search',
                'modelClass' => $modelClass,
                'searchModelClass' => $searchModelClass,
                'interactive' => 0,
                'overwrite' => 1
            ];
        }
    }

    public function actionSearchModels()
    {
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly\search' : 'app\models\search';
            $modelName = Inflector::classify($tableName);
            $modelClass = $ns . '\\' . $modelName . 'SearchBase';
            $secondModelClass = $ns . '\\' . $modelName . 'Search';
            $this->_commands[$secondModelClass] = [
                'gii/search2',
                'modelClass' => $modelClass,
                'secondModelClass' => $secondModelClass,
                'interactive' => 0,
                'overwrite' => 0
            ];
        }
    }

    public function actionAllModels()
    {
        $this->actionBaseModels();
        $this->actionModels();
        $this->actionBaseSearchModels();
        $this->actionSearchModels();
    }

    public function actionDbSchema()
    {
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
    }

    public function actionGenerate()
    {
        $this->actionDbSchema();
        $basePath = Yii::$app->getBasePath();
        foreach ($this->_savedCommands as $targetClass => $args) {
            $this->stdout('Generating: ' . $targetClass . "\n", Console::BOLD, Console::FG_CYAN);
            $command = $basePath . '/yii ' . escapeshellarg(array_shift($args)) . ' --' . vsprintf(implode('=%s --', array_keys($args)) . '=%s', array_map('escapeshellarg', array_values($args)));
            $this->stdout('Executing: ' . $command . "\n", Console::FG_CYAN);
            passthru($command);
        }
    }

    public function actionIndex()
    {
        passthru(Yii::$app->getBasePath() . '/yii help ' . $this->id);
    }
}
