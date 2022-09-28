<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\Response;
use common\models\User;
use yii\web\Controller;
use api\models\SignupForm;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

/**
 * Default controller for the `v1` module
 */
class AuthController extends Controller
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

  public function beforeAction($action) 
  { 
      $this->enableCsrfValidation = false; 
      return parent::beforeAction($action); 
  }
  

        protected function verbs()
        {
            return [
                'login' => ['POST'],
                // 'data' => ['POST'],
                'signup' => ['POST'],
            ];
        }
    /**
     * Renders the index view for the module
     * @return string
     */
    // public function actionIndex()
    // {
    //     return $this->render('index');
    // }

    public function actionLogin(){
      
      
      $post = Yii::$app->request->post();
      
            // $model = (new \yii\db\Query())
            // ->from('user')
            // ->join('members', 'user.email = members.email')
            // ->select('user.username','username.email','members.MemberNo')
            // ->where(['username.email' => $post['noanggota/email']])
            // ->orWhere(['members.MemberNo' => $params['noanggota/email']])
            // ->one();
      $model = User::findOne(["email" => $post["email"]]);
      if (empty($model)) {
          throw new \yii\web\NotFoundHttpException('User not found');
      }
      if ($model->validatePassword($post["password"])) {
          $model->access_token = Yii::$app->getSecurity()->generateRandomString();
          $model->save(false);
          $token = $model->access_token;
          Yii::$app->response->format = Response::FORMAT_JSON;
          return $model; //return whole user model including auth_key or you can just return $model["auth_key"];
      } else {
        Yii::$app->response->format = Response::FORMAT_JSON;
          throw new \yii\web\ForbiddenHttpException();
      }
    }

    public function actionLogout(){
      $user_id = Yii::$app->user->id;
      $model = User::findOne(['id' => $user_id]);
      if(!empty($model)){
        $model->auth_key=NULL;
        $model->access_token=NULL;
        $model->save(false);
        $response = 
        [
          'id' => $user_id,
          'status' => 'berhasil Logout',
        ];
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $response;
      }
      Yii::app()->user->logout(false);
      // $userModel = User::find()->where(['access_token'=>$userID])->one();
      // if(!empty($userModel))
      // {
      //  $userModel->token=NULL;
      //  $userModel->save(false);
      // }
      // Yii::app()->user->logout(false);
    }

    public function actionSignup(){
     

      


      $model = new SignupForm();
      $params = Yii::$app->request->post();
      $model->username = $params['username'];
      $model->password=$params['password'];
      $model->email=$params['email'];
      
      $command = Yii::$app->db->createCommand();
      $command->insert('members', array(
          'Fullname'=>$params['username'],
          'Email' => $params['email'],  
          'Sex_id'=>$params['jeniskelamin'],
          'PlaceOfBirth' => $params['tempatlahir'],
          'DateOfBirth' => $params['tanggallahir'],
          'IdentityType_id' => $params['jenisidentitas'],
          'IdentityNo' => $params['noidentitas'],
          'Address' => $params['alamat'],
          'RT' => $params['rt'],
          'RW' => $params['rw'],
          'RTNow' => $params['rt'],
          'RWNow' => $params['rw'],
          'Kecamatan' => $params['kecamatan'],
          
      ))->execute();


      if ($model->signup()) {
        
          $response['isSuccess'] = 201;
          $response['message'] = 'Berhasil Registrasi!';
          $response['model'] = $model;

          // $response['user'] =\common\models\User::findByUsername($model->username);
          Yii::$app->response->format = Response::FORMAT_JSON;
          return $response;   
      }else {
        //$model->validate();
          $model->getErrors();
          $response['hasErrors'] = $model->hasErrors();
          $response['errors'] = $model->getErrors();
        //return = $model;
           Yii::$app->response->format = Response::FORMAT_JSON;
          return $response;

          }
      
    }
}
