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

namespace BaksDev\Wildberries\Api\Token\Stocks;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;

final class WildberriesStocks extends Wildberries
{

    private array $content = [];

    /**
     * Идентификатор склада продавца
     */
    private ?int $warehouse = null;

    /**
     * Список идентификаторов баркодов
     */
    private ?array $barcode = null;


    /**
     * ID склада продавца
     */
    public function warehouse(int $warehouse): self
    {
        $this->warehouse = $warehouse;

        return $this;
    }


    public function resetBarcode(): self
    {
        $this->barcode = null;
        return $this;
    }



    /**
     * Добавить в список идентификатор баркод
     */
    public function addBarcode(string $barcode): self
    {
        $this->barcode[] = $barcode;
        return $this;
    }


    /**
     * Получить остатки товаров
     *
     * @see https://openapi.wildberries.ru/#tag/Marketplace-Sborochnye-zadaniya/paths/~1api~1v3~1supplies~1{supplyId}~1barcode/get
     *
     */
    public function request(): self
    {

        if($this->warehouse === null)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор склада продавца через вызов метода warehouse: ->warehouse(1234567890)'
            );
        }

        if(empty($this->barcode))
        {
            throw new InvalidArgumentException(
                'Не указан cписок идентификаторов баркодов через вызов метода addBarcode: ->addBarcode("BarcodeTest123")'
            );
        }

        $data = ["skus" => $this->barcode];

        $response = $this->TokenHttpClient()->request(
            'POST',
            '/api/v3/stocks/'.$this->warehouse,
            ['json' => $data],
        );

        if($response->getStatusCode() !== 200)
        {
            $content = $response->toArray(false);

            dump($data);

            //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');
            throw new DomainException(
                message: $response->getStatusCode().': '.$content['message'] ?? self::class, code: $response->getStatusCode()
            );
        }

        $content = $response->toArray(false);

        $this->content = $content['stocks'];

        return $this;
    }


    /**
     * Возвращает остатки товаров.
     *
     * "sku": "BarcodeTest123", - Баркод
     * "amount": 10 - Остаток
     */
    public function getContent(): array
    {
        return $this->content;
    }
}