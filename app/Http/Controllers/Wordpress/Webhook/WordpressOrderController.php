<?php

namespace App\Http\Controllers\Wordpress\webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;


class WordpressOrderController extends Controller
{
    public function handleOrderCreated(array $payload): void
    {
        // Map WP payload into your local DB model
//        Order::create([
//
//        ]);



//        $orderRecord = Order::updateOrCreate(
//            [
//                'wordpress_id' => $payload['id'],
//                'customer_id' => $payload['customer_id'] ?? null,
//                'payment_status' => $payload->mapPaymentStatus($payload),
//                'order_status' => $payload->mapOrderStatus($payload),
//                'payment_method' => $payload['payment_method'] ?? null,
//                'order_amount' => (float)$payload['total'],
//                'discount_amount' => (float)$payload['discount_total'],
//                'shipping_cost' => (float)$payload['shipping_total'],
//                'created_at' => $payload['date_created'],
//                'updated_at' => $payload['date_modified'],
//                'shipping_address' => json_encode($payload['shipping']),
//                'billing_address_data' => json_encode($payload['billing']),
//                'order_note' => $payload['customer_note'] ?? null,
//                'transaction_ref' => $payload['transaction_id'] ?? null,
//                'coupon_code' => $this->extractCouponCode($payload),
//                'is_notified' => false
//            ]
//        );





        $orderRecord = Order::updateOrCreate(
            [
                'wordpress_id' => $payload['id'],
            ],
            [
                'customer_id' => $payload['customer_id'] ?? null,
                'payment_status' => $this->mapPaymentStatus($payload),
                'order_status' => $this->mapOrderStatus($payload),
                'payment_method' => $payload['payment_method'] ?? null,
                'order_amount' => (float)$payload['total'],
                'discount_amount' => (float)$payload['discount_total'],
                'shipping_cost' => (float)$payload['shipping_total'],
                'created_at' => $payload['date_created'],
                'updated_at' => $payload['date_modified'],
                'shipping_address' => json_encode($payload['shipping']),
                'billing_address_data' => json_encode($payload['billing']),
                'order_note' => $payload['customer_note'] ?? null,
                'transaction_ref' => $payload['transaction_id'] ?? null,
                'coupon_code' => $this->extractCouponCode($payload),
                'is_notified' => false,
            ]
        );



        if (isset($payload['line_items']) && is_array($payload['line_items'])) {

            // Optional: delete existing details if re-syncing
            $orderRecord->details()->delete();

            foreach ($payload['line_items'] as $item) {

                // Find product (it may exist in Product or DigitalProductVariation)
                $product = \App\Models\Product::where('wordpress_id', $item['product_id'])->first()
                    ?? \App\Models\DigitalProductVariation::where('wordpress_id', $item['variation_id'])->first();

                // Create order detail
                OrderDetail::updateOrCreate(
                    [
                        'order_id' => $orderRecord->id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'],
                    ],
                    [
                        'product_details' => json_encode($item),
                        'price' => (float)$item['price'],
                        'qty' => (int)$item['quantity'],
                        'discount' => ((float)$item['subtotal'] - (float)$item['total']) / (float)$item['subtotal'] * 100,
                        'tax' => (float)$item['total_tax'] ?? 0,
                        'tax_model' => $item['tax_class'] ?? null,
//                        'delivery_status' => ' ',
//                        'payment_status' => $orderRecord->payment_status,
//                        'seller_id' => $product->seller_id ?? null,
//                        'variant' => $item['variation_id'] ?? null,
                        'variation_id' => $item['variation_id'] ?? null,
                        'buy_together_id' => null,
//                        'buy_together_discount' => 0,
                        'buy_together_price' => 0,
                        'selected_buy_together_ids' => null,
                    ]
                );
            }
        }




    }

    private function extractCouponCode($orderData)
    {
        if (!empty($orderData['coupon_lines'])) {
            return $orderData['coupon_lines'][0]['code'] ?? null;
        }
        return null;
    }
    private function mapOrderStatus($orderData)
    {
        $statusMap = [
            'pending' => 'pending',
            'processing' => 'confirmed',
            'on-hold' => 'confirmed',
            'completed' => 'delivered',
            'cancelled' => 'canceled',
            'refunded' => 'returned'
        ];

        return $statusMap[$orderData['status']] ?? 'pending';
    }
    private function mapPaymentStatus($orderData)
    {
        if ($orderData['date_paid']) {
            return 'paid';
        }
        return $orderData['status'] === 'pending' ? 'unpaid' : 'pending';
    }

    public function handleOrderUpdated(array $payload): void
    {
        $order = Order::where('wordpress_id', $payload['id'])->first();

        if ($order) {
            $order->update([

            ]);
        }
    }

    public function handleOrderDeleted(array $payload): void
    {
        // Mark deleted locally or actually remove
        Order::where('wordpress_id', $payload['id'])->delete();
    }
}


//payload

//{
//    "id": 44482,
//  "parent_id": 0,
//  "status": "processing",
//  "currency": "EGP",
//  "version": "9.7.1",
//  "prices_include_tax": false,
//  "date_created": "2025-10-22T11:25:48",
//  "date_modified": "2025-10-22T11:25:52",
//  "discount_total": "220",
//  "discount_tax": "0",
//  "shipping_total": "0",
//  "shipping_tax": "0",
//  "cart_tax": "0",
//  "total": "15322",
//  "total_tax": "0",
//  "customer_id": 8037,
//  "order_key": "wc_order_GUOCk3us04Sja",
//  "billing": {
//    "first_name": "test",
//    "last_name": "test",
//    "company": null,
//    "address_1": "test",
//    "address_2": null,
//    "city": "Dokki",
//    "state": "GIZA",
//    "postcode": null,
//    "country": "EG",
//    "email": "app@maximfood.com",
//    "phone": "01012121212"
//  },
//  "shipping": {
//    "first_name": "test",
//    "last_name": "test",
//    "company": null,
//    "address_1": "test",
//    "address_2": null,
//    "city": "Dokki",
//    "state": "Giza",
//    "postcode": null,
//    "country": "EG",
//    "phone": "01012121212"
//  },
//  "payment_method": "cod",
//  "payment_method_title": "Cash on delivery",
//  "transaction_id": null,
//  "customer_ip_address": "102.189.26.247",
//  "customer_user_agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36",
//  "created_via": "checkout",
//  "customer_note": "test order",
//  "date_completed": null,
//  "date_paid": null,
//  "cart_hash": "c67c3f1c785347eeac54c8d27ed0aeb3",
//  "number": "44482",
//  "meta_data": [
//    {
//        "id": 595836,
//      "key": "_ga_tracked",
//      "value": "1"
//    },
//    {
//        "id": 595835,
//      "key": "_pys_purchase_event_fired",
//      "value": "1"
//    },
//    {
//        "id": 595812,
//      "key": "_shipping_email",
//      "value": "app@maximfood.com"
//    },
//    {
//        "id": 595813,
//      "key": "_shipping_phone_formatted",
//      "value": null
//    },
//    {
//        "id": 595832,
//      "key": "_wc_order_attribution_device_type",
//      "value": "Desktop"
//    },
//    {
//        "id": 595824,
//      "key": "_wc_order_attribution_referrer",
//      "value": "https://www.google.com/"
//    },
//    {
//        "id": 595830,
//      "key": "_wc_order_attribution_session_count",
//      "value": "46"
//    },
//    {
//        "id": 595827,
//      "key": "_wc_order_attribution_session_entry",
//      "value": "https://maximfood.com/?srsltid=AfmBOopnOVil6XImps8bbsa_qD4C2AOZhz2I7ztw6U1u6OeT6SF681wT"
//    },
//    {
//        "id": 595829,
//      "key": "_wc_order_attribution_session_pages",
//      "value": "12"
//    },
//    {
//        "id": 595828,
//      "key": "_wc_order_attribution_session_start_time",
//      "value": "2025-09-30 06:39:50"
//    },
//    {
//        "id": 595823,
//      "key": "_wc_order_attribution_source_type",
//      "value": "organic"
//    },
//    {
//        "id": 595831,
//      "key": "_wc_order_attribution_user_agent",
//      "value": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36"
//    },
//    {
//        "id": 595826,
//      "key": "_wc_order_attribution_utm_medium",
//      "value": "organic"
//    },
//    {
//        "id": 595825,
//      "key": "_wc_order_attribution_utm_source",
//      "value": "google"
//    },
//    {
//        "id": 595834,
//      "key": "_wf_invoice_date",
//      "value": "1761121548"
//    },
//    {
//        "id": 595837,
//      "key": "_wt_thankyou_action_done",
//      "value": "1"
//    },
//    {
//        "id": 595816,
//      "key": "is_vat_exempt",
//      "value": "no"
//    },
//    {
//        "id": 595820,
//      "key": "pys_enrich_data",
//      "value": {
//        "pys_landing": "https://maximfood.com/",
//        "pys_source": "direct",
//        "pys_utm": "utm_source:undefined|utm_medium:undefined|utm_campaign:undefined|utm_term:undefined|utm_content:undefined",
//        "pys_utm_id": "fbadid:undefined|gadid:undefined|padid:undefined|bingid:undefined",
//        "last_pys_landing": "https://maximfood.com/",
//        "last_pys_source": "direct",
//        "last_pys_utm": "utm_source:undefined|utm_medium:undefined|utm_campaign:undefined|utm_term:undefined|utm_content:undefined",
//        "last_pys_utm_id": "fbadid:undefined|gadid:undefined|padid:undefined|bingid:undefined",
//        "pys_browser_time": "11-12|Wednesday|October"
//      }
//    },
//    {
//        "id": 595821,
//      "key": "pys_fb_cookie",
//      "value": {
//        "fbc": null,
//        "fbp": "fb.1.1752396610453.952643472427572053"
//      }
//    },
//    {
//        "id": 595833,
//      "key": "wf_invoice_number",
//      "value": "44482"
//    },
//    {
//        "id": 595817,
//      "key": "wpml_language",
//      "value": "en"
//    },
//    {
//        "id": 595822,
//      "key": "wt_pklist_order_language",
//      "value": "en_US"
//    }
//  ],
//  "line_items": [
//    {
//        "id": 88138,
//      "name": "Caviale Canadian FRESHLY FROZEN Lobster - 500-650",
//      "product_id": 39380,
//      "c": 39408,
//      "quantity": 4,
//      "tax_class": null,
//      "subtotal": "7800",
//      "subtotal_tax": "0",
//      "total": "7800",
//      "total_tax": "0",
//      "taxes": [],
//      "meta_data": [
//        {
//            "id": 667870,
//          "key": "pa_weight",
//          "value": "500-650",
//          "display_key": "Weight",
//          "display_value": "500-650"
//        }
//      ],
//      "sku": null,
//      "price": 1950,
//      "image": {
//        "id": 39411,
//        "src": "https://maximfood.com/wp-content/uploads/2025/08/lobster-freshly-frozen-scaled.png"
//      },
//      "parent_name": "Caviale Canadian FRESHLY FROZEN Lobster"
//    },
//    {
//        "id": 88139,
//      "name": "Head on Shell on Large Shrimp 1kg",
//      "product_id": 36058,
//      "variation_id": 0,
//      "quantity": 3,
//      "tax_class": null,
//      "subtotal": "3600",
//      "subtotal_tax": "0",
//      "total": "3540",
//      "total_tax": "0",
//      "taxes": [],
//      "meta_data": [],
//      "sku": null,
//      "price": 1180,
//      "image": {
//        "id": "34982",
//        "src": "https://maximfood.com/wp-content/uploads/2025/06/hoso-shrimp-1-scaled.png"
//      },
//      "parent_name": null
//    },
//    {
//        "id": 88140,
//      "name": "Caviale Gold Butterfly Shrimp – Ready To Fry 250gm (Spicy)",
//      "product_id": 30443,
//      "variation_id": 0,
//      "quantity": 2,
//      "tax_class": null,
//      "subtotal": "858",
//      "subtotal_tax": "0",
//      "total": "818",
//      "total_tax": "0",
//      "taxes": [
//        {
//            "id": 1,
//          "total": "0",
//          "subtotal": "0"
//        }
//      ],
//      "meta_data": [],
//      "sku": null,
//      "price": 409,
//      "image": {
//        "id": "30445",
//        "src": "https://maximfood.com/wp-content/uploads/2025/03/butterfly-spicy-01.png"
//      },
//      "parent_name": null
//    },
//    {
//        "id": 88141,
//      "name": "Nigiri Tuna roll - 1 pc",
//      "product_id": 12551,
//      "variation_id": 12552,
//      "quantity": 1,
//      "tax_class": null,
//      "subtotal": "39",
//      "subtotal_tax": "0",
//      "total": "19",
//      "total_tax": "0",
//      "taxes": [
//        {
//            "id": 1,
//          "total": "0",
//          "subtotal": "0"
//        }
//      ],
//      "meta_data": [
//        {
//            "id": 667898,
//          "key": "pieces",
//          "value": "1 pc",
//          "display_key": "Pieces",
//          "display_value": "1 pc"
//        }
//      ],
//      "sku": "745",
//      "price": 19,
//      "image": {
//        "id": 12553,
//        "src": "https://maximfood.com/wp-content/uploads/2023/07/tuna-nigiri.png"
//      },
//      "parent_name": "Nigiri Tuna roll"
//    },
//    {
//        "id": 88142,
//      "name": "Caviale Salmon Fillet Portion 400gm",
//      "product_id": 9248,
//      "variation_id": 0,
//      "quantity": 5,
//      "tax_class": null,
//      "subtotal": "3245",
//      "subtotal_tax": "0",
//      "total": "3145",
//      "total_tax": "0",
//      "taxes": [],
//      "meta_data": [],
//      "sku": "6224010194410",
//      "price": 629,
//      "image": {
//        "id": "9464",
//        "src": "https://maximfood.com/wp-content/uploads/2022/04/1-01-1.png"
//      },
//      "parent_name": null
//    }
//  ],
//  "tax_lines": [
//    {
//        "id": 88144,
//      "rate_code": "EG-TAX-1",
//      "rate_id": 1,
//      "label": "Tax",
//      "compound": false,
//      "tax_total": "0",
//      "shipping_tax_total": "0",
//      "rate_percent": 0,
//      "meta_data": []
//    }
//  ],
//  "shipping_lines": [
//    {
//        "id": 88143,
//      "method_title": "Free shipping",
//      "method_id": "free_shipping",
//      "instance_id": "12",
//      "total": "0",
//      "total_tax": "0",
//      "taxes": [],
//      "tax_status": "taxable",
//      "meta_data": [
//        {
//            "id": 667913,
//          "key": "Items",
//          "value": "Caviale Canadian FRESHLY FROZEN Lobster - 500-650 &times; 4, Head on Shell on Large Shrimp 1kg &times; 3, Caviale Gold Butterfly Shrimp – Ready To Fry 250gm (Spicy) &times; 2, Nigiri Tuna roll - 1 pc &times; 1, Caviale Salmon Fillet Portion 400gm &times; 5",
//          "display_key": "Items",
//          "display_value": "Caviale Canadian FRESHLY FROZEN Lobster - 500-650 &times; 4, Head on Shell on Large Shrimp 1kg &times; 3, Caviale Gold Butterfly Shrimp – Ready To Fry 250gm (Spicy) &times; 2, Nigiri Tuna roll - 1 pc &times; 1, Caviale Salmon Fillet Portion 400gm &times; 5"
//        }
//      ]
//    }
//  ],
//  "fee_lines": [],
//  "coupon_lines": [
//    {
//        "id": 88145,
//      "code": "ameramer",
//      "discount": "220",
//      "discount_tax": "0",
//      "meta_data": [
//        {
//            "id": 667922,
//          "key": "coupon_info",
//          "value": "[44274,\"ameramer\",\"fixed_product\",20,true]",
//          "display_key": "coupon_info",
//          "display_value": "[44274,\"ameramer\",\"fixed_product\",20,true]"
//        }
//      ],
//      "discount_type": "fixed_product",
//      "nominal_amount": 20,
//      "free_shipping": true
//    }
//  ],
//  "refunds": [],
//  "payment_url": "https://maximfood.com/checkout/order-pay/44482/?pay_for_order=true&key=wc_order_GUOCk3us04Sja",
//  "is_editable": false,
//  "needs_payment": false,
//  "needs_processing": true,
//  "date_created_gmt": "2025-10-22T08:25:48",
//  "date_modified_gmt": "2025-10-22T08:25:52",
//  "date_completed_gmt": null,
//  "date_paid_gmt": null,
//  "currency_symbol": "EGP",
//  "_links": {
//    "self": [
//      {
//          "href": "https://maximfood.com/wp-json/wc/v3/orders/44482",
//        "targetHints": {
//          "allow": [
//              "GET",
//              "POST",
//              "PUT",
//              "PATCH",
//              "DELETE"
//          ]
//        }
//      }
//    ],
//    "collection": [
//      {
//          "href": "https://maximfood.com/wp-json/wc/v3/orders"
//      }
//    ],
//    "customer": [
//      {
//          "href": "https://maximfood.com/wp-json/wc/v3/customers/8037"
//      }
//    ]
//  }
//}