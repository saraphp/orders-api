<?php

namespace App\Http\Controllers;


use App\Models\Item;
use App\Models\ItemTag;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;


class OrderController extends Controller
{


    /**author sara rabie
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function getOrder(Request $request)
    {

        return view('orders.index');

    }

    /**
     * to store order in database and get discount value
     * @param Request $request
     * @return string
     */

    public function storeOrder(Request $request)
    {

       $request= '{
                "order": {
                  "order_id": 51275,
                  "email": "test@email.com",
                  "total_amount_net": "1890.00",
                  "shipping_costs": "29.00",
                  "payment_method": "VISA",
                  "items": [
                    {
                      "name": "Item1",
                      "qnt": 1,
                      "value": 1100,
                      "category": "Fashion",
                      "subcategory": "Jacket",
                      "tags": [
                        "porsche",
                        "design"
                      ],
                      "collection_id": 12
                    },
                    {
                      "name": "Item2",
                      "qnt": 1,
                      "value": 790,
                      "category": "Watches",
                      "subcategory": "sport",
                      "tags": [
                        "watch",
                        "porsche",
                        "electronics"
                      ],
                      "collection_id": 7
                    }
                  ]
                }
            }';

        $decodedRequest = json_decode($request,true);
        $orderData = $decodedRequest['order'];
        $itemsData = $orderData['items'];
        $totalDiscountValue =0;
        $totalDiscountPercent=0;
        $collectionFlag =0;
      DB::beginTransaction();
      try {
          $order = Order::create([
              'order_id' => $orderData['order_id'],
              'email' => $orderData['email'],
              'total_amount_net' => $orderData['total_amount_net'],
              'shipping_costs' => $orderData['shipping_costs'],
              'payment_method' => $orderData['payment_method'],
              'total_discount_value' => $totalDiscountValue,

          ]);
          foreach ($itemsData as $item) {
              $itemDiscountValue = 0;
              // to get discount value debend on its collection
              if ($item['collection_id']) {
                  $collectionFlag +=1;
                  $itemDiscountValue = $this->getDiscountValue($item['collection_id']);
                  $totalDiscountPercent += $itemDiscountValue;
              }
              $itemResult = Item::create([
                  'name' => $item['name'],
                  'qnt' => $item['qnt'],
                  'value' => $item['value'],
                  'category' => $item['category'],
                  'subcategory' => $item['subcategory'],
                  'collection_id' => $item['collection_id'],
                'discount_value' => $itemDiscountValue,
                  'order_id' => $orderData['order_id']
              ]);
              foreach ($item['tags'] as $tag) {
                  $itemtag = ItemTag::create([
                      'name' => $tag,
                      'item_id' => $itemResult['id'],

                  ]);
              }
          }
          //this code if get discount value only for order
         // if($collectionFlag > 0) {
              //$totalDiscountPercent = $this->getDiscountValue();
              //$totalDiscountPercent += $itemDiscountValue;

              if ($totalDiscountPercent > 25) {
                  $totalDiscountPercent = 25;
              }
              $totalDiscountValueCost = ($orderData['total_amount_net'] + $orderData['shipping_costs']) * ((100 - $totalDiscountPercent) / 100);
              $totalDiscountValue = ($orderData['total_amount_net'] + $orderData['shipping_costs']) - $totalDiscountValueCost;
              Order::where('id', $order['id'])->update([
                  'total_discount_value' => $totalDiscountValue,
              ]);
          //}

          DB::commit();
          return " success created order";
      }catch (Exception $exception){
          DB::rollback();
          return $exception->getMessage();
      }

    }

    /**
     * Get discount value debend on collection id
     */
    public function getDiscountValue($collectio_id){
        $keyword = 'status';
       if($collectio_id == 12){
           $keyword = 'Status';
       }
       $output = file_get_contents("https://developer.github.com/v3/#http-redirects");
        //echo $count_keys = preg_match_all('/\bstatus/', strip_tags($output));

//                // create curl resource
//                $ch = curl_init();
//
//                // set url
//                curl_setopt($ch, CURLOPT_URL, "https://developer.github.com/v3/#http-redirects");
//
//                //return the transfer as a string
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//
//                // $output contains the output string
//                $output = curl_exec($ch);
//
//                // close curl resource to free up system resources
//                curl_close($ch);
                $count  =substr_count(strip_tags($output),$keyword);
                return $count;


    }


}
