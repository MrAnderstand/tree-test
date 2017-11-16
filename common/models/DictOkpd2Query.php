<?php

namespace common\models;

use creocoder\nestedsets\NestedSetsQueryBehavior;

class DictOkpd2Query extends \yii\db\ActiveQuery
{
    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}
