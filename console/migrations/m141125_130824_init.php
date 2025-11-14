<?php

use yii\db\Migration;

class m141125_130824_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('Идентификатор пользователя'),
            'amount' => $this->integer()->notNull()->comment('Сумма займа'),
            'term' => $this->integer()->notNull()->comment('Срок займа в днях'),
            'status' => $this->tinyInteger()->defaultValue(0)->notNull()->comment('Статус заявки'),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%request}}');
    }
}
