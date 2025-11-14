<?php

namespace console\jobs;

use Yii;
use yii\base\BaseObject;
use common\models\Request;
use yii\queue\JobInterface;

class ProcessRequestJob extends BaseObject implements JobInterface
{
    public int $userId;
    public int $delay;

    /**
     * {@inheritdoc}
     */
    public function execute($queue): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $requests = Request::find()
                ->where(['user_id' => $this->userId, 'status' => Request::STATUS_NEW])
                ->all();

            $hasApproved = Request::find()
                ->where(['user_id' => $this->userId, 'status' => Request::STATUS_APPROVED])
                ->exists();

            foreach ($requests as $request) {
                $innerTransaction = Yii::$app->db->beginTransaction();
                try {
                    $lockedRequest = Request::findBySql(
                        'SELECT * FROM ' . Request::tableName() . ' WHERE id = :id AND status = :status FOR UPDATE',
                        [
                            ':id' => $request->id,
                            ':status' => Request::STATUS_NEW
                        ]
                    )->one();

                    if (!$lockedRequest) {
                        $innerTransaction->commit();
                        continue;
                    }

                    if ($hasApproved) {
                        $lockedRequest->status = Request::STATUS_DECLINED;
                    } else {
                        $lockedRequest->status = mt_rand(1, 100) <= 10 ? Request::STATUS_APPROVED : Request::STATUS_DECLINED;
                        if ($lockedRequest->status === Request::STATUS_APPROVED) {
                            $hasApproved = true;
                        }
                    }

                    sleep($this->delay);
                    $lockedRequest->save(false);
                    echo "Processed request ID {$request->id} for user {$this->userId} with status {$lockedRequest->getStatusLabel()}\n";
                    $innerTransaction->commit();
                } catch (\Exception $e) {
                    $innerTransaction->rollBack();
                    echo "Failed to process request ID {$request->id}: {$e->getMessage()}\n";
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo "Failed to process requests for user {$this->userId}: {$e->getMessage()}\n";
        }
    }
}