<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "request".
 *
 * @property int $id
 * @property int $user_id Идентификатор пользователя
 * @property int $amount Сумма займа
 * @property int $term Срок займа в днях
 * @property int $status Статус заявки
 */
class Request extends ActiveRecord
{
    public const STATUS_NEW = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_DECLINED = 3;

    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_IN_PROGRESS => 'В обработке',
        self::STATUS_APPROVED => 'Одобрен',
        self::STATUS_DECLINED => 'Отклонен',
    ];

    public const SCENARIO_CREATE = 'create';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['status'], 'default', 'value' => 0],
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term', 'status'], 'default', 'value' => self::STATUS_NEW],
            [['user_id', 'amount', 'term', 'status'], 'integer'],
            [['amount', 'term'], 'number', 'min' => 1],
            [['user_id'], 'validateNoApprovedRequests', 'on' => self::SCENARIO_CREATE],
        ];
    }

    /**
     * @param $attribute
     * @return void
     */
    public function validateNoApprovedRequests($attribute): void
    {
        if (self::find()->where(['user_id' => $this->$attribute, 'status' => self::STATUS_APPROVED])->exists()) {
            $this->addError($attribute, 'Пользователь уже имеет одобренную заявку.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Идентификатор пользователя',
            'amount' => 'Сумма займа',
            'term' => 'Срок займа в днях',
            'status' => 'Статус заявки',
        ];
    }

    /**
     * @return string
     */
    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? '';
    }
}
