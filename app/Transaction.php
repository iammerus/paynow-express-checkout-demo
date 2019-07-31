<?php
/**
 * Created by PhpStorm.
 * User: Melvin
 * Date: 16/7/2019
 * Time: 04:03
 */

namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Transaction extends Eloquent
{
    protected $fillable = ['payment_model', 'payment_action', 'payment_record', 'payment_column', 'amount', 'value'];
}