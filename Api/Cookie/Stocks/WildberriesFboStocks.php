<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Wildberries\Api\Cookie\Stocks;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;

final class WildberriesFboStocks extends Wildberries
{

    private int $limit = 100;

    private int $offset = 0;

    private array $content;


    public function withLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }


    public function withOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }


    /**
     * Возвращает список товаров поставщика с их остатками на складе FBO
     * method: POST
     * url: //ns/balances/analytics-back/api/v1/balances
     *
     * Позволяет получить массив товаров с их остатками по складам
     * barcode - штрихкод товара
     * sort - какие columns и с какими excludedValues исключить.
     * find - поиск карточке с определённым search(значением) в определённом columns.
     */
    public function getAllStocks()
    {

        $jsonParsedArray = [
            "filters" => [
                //"supplierArticle",
                "barcode",
                "quantityInTransit",
                "quantityForSaleTotal",
            ],
        ];

        $requestJson = json_encode($jsonParsedArray);

        $response = $this->CookieHttpClient()
            ->request(
                'POST',
                '/ns/balances/analytics-back/api/v1/balances',
                [
                    'query' => [
                        'limit'  => $this->limit,
                        'offset' => $this->offset,
                    ],
                    'body'  => $requestJson,
                ],
            );

        if($response->getStatusCode() !== 200)
        {
            //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');
            throw new DomainException(
                message: $content['message'] ?? self::class, code: $response->getStatusCode()
            );
        }

        $this->content = $response->toArray(false);

        return $this;
    }


    /**
     * @return null
     */
    public function getContent()
    {
        return $this->content;
    }
}