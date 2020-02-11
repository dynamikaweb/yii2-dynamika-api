<?php
/**
 * @link https://github.com/yiisoft/yii2/blob/master/framework/filters/ContentNegotiator.php
 */
namespace dynamikaweb\api\behaviors;


class ContentNegotiator extends \yii\filters\ContentNegotiator
{
    public $formatDefault = \yii\web\Response::FORMAT_JSON;

    protected function negotiateContentType($request, $response)
    {
        if(!$request->get($this->formatParam)){
            $request->setQueryParams([$this->formatParam => $this->formatDefault]);
        }

        $response->format = $this->formatDefault;
      
        parent::negotiateContentType($request, $response);       
    }
}