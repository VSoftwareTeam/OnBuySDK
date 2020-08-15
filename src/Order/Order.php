<?php
/**
 * Copyright © 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Xigen\Library\OnBuy\Order;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Headers;
use Laminas\Json\Json;
use Xigen\Library\OnBuy\Constants;

class Order extends Constants
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var \Laminas\Http\Headers
     */
    protected $headers;

    /**
     * @var \Laminas\Http\Client;
     */
    protected $client;

    /**
     * @var \Laminas\Http\Response
     */
    protected $response;

    /**
     * Site constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->headers = new Headers();
        $this->client = new Client();
        $this->client->setOptions([
            'maxredirects' => self::MAXREDIRECTS,
            'timeout' => self::TIMEOUT,
        ]);
        $this->token = $token;
        $this->headers->addHeaderLine('Authorization', $this->token);
        $this->client->setHeaders($this->headers);
    }
    /**
     * Browse and search orders
     * @param array $filterArray status[awaiting_dispatch|dispatched|complete|cancelled|cancelled_by_seller|cancelled_by_buyer|partially_dispatched|partially_refunded|refunded|all]|order_ids|modified_since|previously_exported
     * @param $sort string asc|desc
     * @return mixed
     * @throws \Exception
     */
    public function getOrder($filterArray = [], $sort = null)
    {
        $this->client->setUri($this->domain . $this->version . self::ORDERS);
        $this->client->setMethod(Request::METHOD_GET);

        // required
        $params = [
            'site_id' => self::SITE_ID
        ];

        // optional
        if (!empty($filterArray)) {
            $params['filter'] = $filterArray;
        }
        if ($sort) {
            $params['sort'] = $sort;
        }

        $this->client->setParameterGet($params);
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * View order by ID
     * @param $orderId int
     * @return mixed
     * @throws \Exception
     */
    public function getOrderById($orderId = null)
    {
        if (empty($orderId)) {
            throw new \Exception('Order ID required');
        }
        $this->client->setUri($this->domain . $this->version . self::ORDERS . '/' . $orderId);
        $this->client->setMethod(Request::METHOD_GET);
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * Full or partial dispatched - if only order number is given full dispatch
     * @param array $updateArray order_id|products[sku|opc|quantity]|tracking[tracking_id|supplier_name|number|url]
     * @return mixed
     * @throws \Exception
     */
    public function dispatchOrder($updateArray = [])
    {
        $this->client->setUri($this->domain . $this->version . self::ORDERS . '/' . self::DISPATCH);
        $this->client->setMethod(Request::METHOD_PUT);
        $this->client->setRawBody(Json::encode([
            'orders' => $updateArray
        ]));
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * Refund a cancelled order
     * @param array $cancelArray order_id|order_cancellation_reason_id[1|2|3|4|5]|cancel_order_additional_info
     * @return mixed
     * @throws \Exception
     */
    public function cancelOrder($cancelArray = [])
    {
        $this->client->setUri($this->domain . $this->version . self::ORDERS . '/' . self::CANCEL);
        $this->client->setMethod(Request::METHOD_PUT);
        $this->client->setRawBody(Json::encode([
            'site_id' => self::SITE_ID,
            'orders' => $cancelArray
        ]));
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * Refund a cancelled order
     * @param array $refundArray order_id|order_refund_reason_id[1|2|3|4|5]|cancel_order_additional_info
     * @return mixed
     * @throws \Exception
     */
    public function refundOrder($refundArray = [])
    {
        $this->client->setUri($this->domain . $this->version . self::ORDERS . '/' . self::CANCEL);
        $this->client->setMethod(Request::METHOD_PUT);
        $this->client->setRawBody(Json::encode([
            'site_id' => self::SITE_ID,
            'orders' => $refundArray
        ]));
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * Get tracking providers
     * @return mixed
     * @throws \Exception
     */
    public function getTrackingProviders()
    {
        $this->client->setUri($this->domain . $this->version . self::ORDERS . '/' . self::TRACKING_PROVIDERS);
        $this->client->setMethod(Request::METHOD_GET);
        $this->client->setParameterGet([
            'site_id' => self::SITE_ID
        ]);
        $this->response = $this->client->send();
        $this->catchError($this->response);
        return Json::decode($this->response->getBody(), Json::TYPE_ARRAY);
    }
}