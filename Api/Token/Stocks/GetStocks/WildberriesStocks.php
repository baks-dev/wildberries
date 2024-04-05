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

namespace BaksDev\Wildberries\Api\Token\Stocks\GetStocks;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class WildberriesStocks extends Wildberries
{
    /**
     * Идентификатор склада продавца
     */
    private ?int $warehouse = null;

    /**
     * Список идентификаторов баркодов
     */
    private array $barcode = [];

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
        $this->barcode = [];
        return $this;
    }


    /**
     * Добавить в список идентификатор баркод
     */
    public function addBarcode(string $barcode): self
    {
        if(!in_array($barcode, $this->barcode, true))
        {
            $this->barcode[] = $barcode;
        }

        return $this;
    }

    /**
     * Получить остатки товаров
     *
     * @see https://openapi.wildberries.ru/marketplace/api/ru/#tag/Ostatki/paths/~1api~1v3~1stocks~1{warehouseId}/post
     *
     */
    public function stocks(): Stocks
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

        $cache = new FilesystemAdapter('wildberries');

        $response = $cache->get('stocks-'.$this->profile->getValue().'-'.implode('.', $this->barcode), function(
            ItemInterface $item
        ) {

            $item->expiresAfter(60 * 5);

            $data = ["skus" => $this->barcode];

            $response = $this->TokenHttpClient()->request(
                'POST',
                '/api/v3/stocks/'.$this->warehouse,
                ['json' => $data],
            );

            if($response->getStatusCode() !== 200)
            {
                $content = $response->toArray(false);

                throw new DomainException(
                    message: $content['message'] ?? self::class, code: $response->getStatusCode()
                );
            }

            return $response;

        });

        $content = $response->toArray(false);

        return new Stocks($content);
    }


    public function dataTest(): array
    {
        return [
            "stocks" => [
                [
                    "sku" => 'BarcodeTest123',
                    "amount" => 5
                ],
                [
                    "sku" => 'BarcodeTest456',
                    "amount" => 10
                ]
            ]
        ];
    }
}