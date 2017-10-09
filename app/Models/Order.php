<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'email', 'total_amount_net','shipping_costs','payment_method','total_discount_value'
    ];


}
