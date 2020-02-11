<?php
/**
 * @link https://github.com/yiisoft/yii2/blob/master/framework/filters/ContentNegotiator.php
 */
namespace dynamikaweb\api\behaviors;

use Yii;

class ApiResponse extends \yii\base\Behavior
{

    public $formatAdd = [];


    public function attach($owner)
    {
        Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) use ($owner) {
          
            $response = $event->sender;
          
            $success = $response->isSuccessful;
            $count = $response->isSuccessful? $owner->count: 0;                                
            $data = $response->data;        
            
          
            if  (!$success) {                
                \yii\helpers\ArrayHelper::remove($data, 'type');
            }
            
           
            if (\Yii::$app->request->get('suppress_response_code')){
                $response->setStatusCode(200);
            }

            $response->data = [
                'success' => $success,
                'count' => $count,
                'data' => $data                    
            ];
        });    
    }
}