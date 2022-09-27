<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;

/**
 * Default controller for the `v1` module
 */
class BookController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
      $rows = (new \yii\db\Query())
      ->from('catalogs')
      ->all();
      $response = [$rows];
      Yii::$app->response->format = Response::FORMAT_JSON;
      return $response;
    }
}
