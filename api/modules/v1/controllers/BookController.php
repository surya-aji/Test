<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * Default controller for the `v1` module
 */
class BookController extends Controller
{

  public function behaviors()
  {
      return ArrayHelper::merge(
          parent::behaviors(), [
              'authenticator' => [
                  'class' => CompositeAuth::className(),
                  'except' => ['login','signup'],
                  'authMethods' => [
                      HttpBasicAuth::className(),
                      HttpBearerAuth::className(),
                      QueryParamAuth::className(),
                  ], 
                  
              ],
          ]
      );
  }
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
      $rows = (new \yii\db\Query())
      ->select(['Title','Author','Edition','Publisher','PublishYear','PublishLocation', 'Note'])
      ->from('catalogs')
      ->orderBy(['CreateDate'=> SORT_DESC])
      ->limit(4)
      ->all();
      $response = [$rows];
      Yii::$app->response->format = Response::FORMAT_JSON;
      return $response;
    }

    public function actionOftenborrowed(){
      // $rows = (new \yii\db\Query())
      // ->select([
      //   'catalogs.Title',
      //   'catalogs.Author',
      //   'catalogs.Edition',
      //   'catalogs.Publisher',
      //   'catalogs.PublishYear',
      //   'catalogs.PublishLocation',
      //   'catalogs.Note',
      //   'collections.ID as collection_id',
      //   'collections.Catalog_id as catalog_id',
      //   // 'COUNT(bookinglogs.*) as booking'
      //   'bookinglogs.memberId',
      //   'bookinglogs.collectionId'
      // ])
      // ->from('catalogs')
      // ->leftjoin('collections','catalogs.id = collections.Catalog_id')
      // ->leftjoin('bookinglogs','collections.ID = bookinglogs.collectionId')
      // ->orderBy(['bookinglogs.collectionId'=> SORT_DESC])
      // ->groupBy(['bookinglogs.collectionId'])
      // ->limit(2)
      // ->all();


      $rows = (new \yii\db\Query())
      ->select([
        'catalogs.Title',
        'catalogs.Author',
        'catalogs.Edition',
        'catalogs.Publisher',
        'catalogs.PublishYear',
        'catalogs.PublishLocation',
        'catalogs.Note',
        'collections.ID as collection_id',
        'collections.Catalog_id as catalog_id',
        // 'COUNT(bookinglogs.*) as booking'
        'bookinglogs.memberId',
        'bookinglogs.collectionId'
      ])
      ->from('bookinglogs')
      ->innerjoin('collections','bookinglogs.collectionId = collections.Catalog_id')
      ->innerjoin('catalogs','collections.catalog_id = catalogs.ID')
      ->orderBy(['bookinglogs.collectionId'=> SORT_DESC])
      ->groupBy(['bookinglogs.collectionId'])
      ->limit(2)
      ->all();


      $response = [$rows];
      Yii::$app->response->format = Response::FORMAT_JSON;
      return $response;
    }

}
