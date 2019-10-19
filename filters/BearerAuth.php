<?php
namespace app\filters;

class BearerAuth extends \yii\filters\auth\HttpBearerAuth
{
    /**
     * {@inheritdoc}
     */
    public function handleFailure($response)
    {
        $resp = \Yii::$app->response;
        $resp->setStatusCode(403);
        $resp->content = json_encode([
            'message' => "You need authorization",
        ]);
        \Yii::$app->end();
    }    
}
