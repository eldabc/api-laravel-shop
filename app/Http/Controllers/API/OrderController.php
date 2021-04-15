<?php

namespace App\Http\Controllers\API;

use App\Models\Plan;
use App\Models\Order;
use App\Models\Product;
use App\Models\ResaleData;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use App\Services\EpaycoService;
use App\Mail\CreatedOrderMailable;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use ErrorException;
use Illuminate\Support\Facades\Mail;

class OrderController extends EpaycoService //Controller
{
    
    private $pay_service;
    public function __construct()
    {
        $this->pay_service = new EpaycoService(); 

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Order::with('order_details')->orderBy('id', 'DESC')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {
        try {
            DB::beginTransaction();
                $validated = $request->validated();
                $validated['status_id'] = 2; //2 = Pendiente

                    $pay = $this->pay_service->createPayment();
                    if ($e = isException($pay))
                        return $e;
                
                $order = Order::create($validated);

                $items = [];
                foreach(json_decode($request->items) as $key => $value){

                    $res = stock($value);
                        if ($res['stock'] < 0){
                            DB::rollBack();
                            return Response('Sin Stock suficiente para la compra '.$res['product']->name .' disponible: '.$res['product']->stock, 500, ['Content-Type' => 'text/plain']);
                        }

                    DB::update(
                        'update '.$res['table'].' set stock = ? where id = ?', [$res['stock'], $res['plan']->product_id]
                    );

                    $orderDetails =  OrderDetails::create([
                            "price" => $value->price,
                            "quantity" => $value->quantity,
                            "plan_id" => $value->plan_id,
                            "distr_id" => $res['distr_id'],
                            "order_id" => $order->id,
                    ]);

                    array_push($items, $orderDetails);
                }
                    $order['order_details'] = $items;

            send_mail_order($order);
            
            DB::commit();
            return response([ 'order' => $order, 'success' => "Pedido Creado"]);
        
        } catch (\Exception $e) {
            DB::rollBack();
            return Response('Ha ocurrido un problema. '.$e->getMessage(), 500, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  users_id $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Order::with('order_details')->where('user_id', $id)->orderBy('id', 'DESC')->get();
    }
}
