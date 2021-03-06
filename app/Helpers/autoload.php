<?php

use App\Models\Plan;
use App\Models\Product;
use App\Models\ResaleData;
use App\Mail\CreatedUserMailable;
use App\Mail\CreatedOrderMailable;
use App\Mail\ChangePasswordMailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

  /**
   * Register exceptions.
   *
   * @param value $r
   * @return stdClass Object token card id
   */
  function isException($r)
  {
    if ( json_encode($r->status) === 'false')
      return json_encode($r->data->description).' '.json_encode($r->data->errors);
      else return false;
  }

   /**
   * Discount stock by role.
   *
   * @param value $value
   * @return array
   */
  function stock($value, $distr_id = '')
  {
    $plan = Plan::findOrFail($value->plan_id);

      if(!empty($distr_id)){ //sale client 
        $product = ResaleData::findOrFail($plan->product_id);
        $table = 'resale_data';

    } else { //sale distribuitor
        $product = Product::findOrFail($plan->product_id);
        $table = 'products';
    }

    return [ 'plan' => $plan, 'product' => $product, 'table' => $table, 'stock' => $product->stock - $value->quantity ];
  }

  /**
   * Update stock in data base.
   *
   * @param $table, $stock, $product_id
   * @return array
   */
  function updateStock($table, $stock, $product_id)
  {
    DB::update(
      'update '.$table.' set stock = ? where id = ?', [$stock, $product_id]
    );
  }

  /**
   * Mail in create order.
   *
   * @param order $order
   * @return array
   */ 
  function send_mail_order($order)
  {
      $data = [
        'name' => $order->name, 
        'phone' => $order->phone, 
        'email' => $order->email, 
        'city' => $order->city, 
        'delivery_address' => $order->delivery_address, 
        'total_order' => $order->total_order, 
        'items' => $order['order_details'] 
      ];
    
    Mail::to($order->email)->send(new CreatedOrderMailable($data));
  }

  /**
   * Mail in create user.
   *
   * @param order $order
   * @return array
   */ 
  function send_mail_user($user)
  {
      $data = [
        'name' => $user->name, 
        'phone' => $user->phone, 
        'email' => $user->email, 
        'delivery_address' => $user->delivery_address, 
        'total_user' => $user->total_user, 
        'items' => $user['order_details'] 
      ];
    
    Mail::to($user->email)->send(new CreatedUserMailable($user));
  }

  /**
   * Mail Change Password.
   *
   * @param email $email, token $token
   * @return array
   */ 
  function send_mail_change_pass($email, $token)
  {
      $data = [
        'email' => $email, 
        'token' => $token
      ];
    
    Mail::to($email)->send(new ChangePasswordMailable($data));
  }