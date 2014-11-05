<?php

namespace yii\kladovka\console;

use yii\console\Controller,
    yii\kladovka\helpers\Log,
    Yii;


class GenerateController extends Controller
{

    public function actionDumpSchema()
    {
        Log::beginMethod(__METHOD__);
        $db = Yii::$app->getDb();
        parse_str(str_replace(';', '&', substr($db->dsn, 6)), $dsnParams);
        $filename = Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $dsnParams['dbname'] . '-schema.sql';
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

    public function actionIndex()
    {
        passthru(getcwd() . '/yii help ' . $this->id);
    }
}
