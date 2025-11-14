<?php

namespace api\controllers;

use common\models\Request;
use console\jobs\ProcessRequestJob;
use Yii;
use yii\rest\Controller;
use OpenApi\Annotations as OA;

class RequestController extends Controller
{
    /**
     * @OA\Post(
     *     path="/requests",
     *     summary="Создать заявку",
     *     tags={"Requests"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount", "term"},
     *             @OA\Property(property="user_id", type="integer", minimum=1, example=1),
     *             @OA\Property(property="amount", type="number", format="float", minimum=1, example=3000),
     *             @OA\Property(property="term", type="integer", minimum=1, example=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Заявка создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="result", type="boolean", example=true),
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="result", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function actionCreate(): array
    {
        $model = new Request();
        $model->setScenario(Request::SCENARIO_CREATE);
        $model->load(Yii::$app->request->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->response->setStatusCode(201);
            return [
                'result' => true,
                'id' => $model->id,
            ];
        }
        Yii::$app->response->setStatusCode(400);
        return [
            'result' => false,
            'errors' => $model->getFirstErrors(),
        ];
    }

    /**
     * @OA\Get(
     *     path="/processor",
     *     summary="Обработать заявки",
     *     tags={"Requests"},
     *     @OA\Parameter(
     *         name="delay",
     *         in="query",
     *         description="Задержка в секундах",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=0, default=0, example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заявки добавлены в очередь",
     *         @OA\JsonContent(
     *             @OA\Property(property="result", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function actionProcess(int $delay = 0): array
    {
        $userIds = Request::find()
            ->where(['status' => Request::STATUS_NEW])
            ->select('user_id')
            ->distinct()
            ->column();

        foreach ($userIds as $userId) {
            Yii::$app->queue->push(new ProcessRequestJob([
                'userId' => $userId,
                'delay' => $delay,
            ]));
        }

        return [
            'result' => true,
        ];
    }
}