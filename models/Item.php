<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2017 Power Kernel
 */

namespace powerkernel\billing\models;

use Yii;

/**
 * This is the model class for Item.
 *
 * @property integer|\MongoDB\BSON\ObjectID|string $id
 * @property string $id_invoice
 * @property string $name
 * @property integer $quantity
 * @property double $price
 * @property string $details
 * @property string $status
 */
class Item extends ItemBase
{

    const STATUS_NOT_SHIPPED = 'STATUS_NOT_SHIPPED'; //10;
    const STATUS_SHIPPED = 'STATUS_SHIPPED'; //10;
    const STATUS_RETURNED = 'STATUS_RETURNED'; //20;

    /**
     * get status list
     * @param null $e
     * @return array
     */
    public static function getStatusOption($e = null)
    {
        $option = [
            self::STATUS_NOT_SHIPPED => Yii::$app->getModule('billing')->t('Not Shipped'),
            self::STATUS_SHIPPED => Yii::$app->getModule('billing')->t('Shipped'),
            self::STATUS_RETURNED => Yii::$app->getModule('billing')->t('Returned'),
        ];
        if (is_array($e))
            foreach ($e as $i)
                unset($option[$i]);
        return $option;
    }

    /**
     * get status text
     * @return string
     */
    public function getStatusText()
    {
        $status = $this->status;
        $list = self::getStatusOption();
        if (!empty($status) && in_array($status, array_keys($list))) {
            return $list[$status];
        }
        return Yii::$app->getModule('billing')->t('Unknown');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value'=>null],
            [['id_invoice', 'name'], 'required'],
            [['quantity'], 'integer'],
            [['price'], 'number'],
            [['details'], 'string'],
            [['id_invoice'], 'string', 'max' => 23],
            [['name', 'status'], 'string', 'max' => 255],
            [['id_invoice'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::className(), 'targetAttribute' => ['id_invoice' => 'id_invoice']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::$app->getModule('billing')->t('ID'),
            'id_invoice' => Yii::$app->getModule('billing')->t('Invoice'),
            'name' => Yii::$app->getModule('billing')->t('Name'),
            'quantity' => Yii::$app->getModule('billing')->t('Quantity'),
            'price' => Yii::$app->getModule('billing')->t('Price'),
            'details' => Yii::$app->getModule('billing')->t('Details'),
            'status' => Yii::$app->getModule('billing')->t('Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id_invoice' => 'id_invoice']);
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->invoice->calculate();
        $this->invoice->save(false);
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }
}
