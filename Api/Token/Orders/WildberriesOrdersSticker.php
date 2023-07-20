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

namespace BaksDev\Wildberries\Api\Token\Orders;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;

final class WildberriesOrdersSticker extends Wildberries
{

    /**
     * Список этикеток
     *
     * "orderId": 5346346,
     * "partA": 231648,
     * "partB": 9753,
     * "barcode": "!uKEtQZVx",
     * "file":
     * "PD94bWwgdmVyc2lvbj0iMS4wIj8+CjwhLS0gR2VuZXJhdGVkIGJ5IFNWR28gLS0+Cjxzdmcgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiCiAgICAgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIgogICAgIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4KPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjQwMCIgaGVpZQiIGhlaWdodD0iMTcwIiBzdHlsZT0iZmlsbDpibGFjayIgLz4KPHJlY3QgeD0iMzE4IiB5PSIyMCIgd2lkdGg9IjYiIGhlaWdodD0iMTcwIiBzdHlsZT0iZmlsbDpibGFjayIgLz4KPHJlY3QgeD0iMzI2IiB5PSIyMCIgd2lkdGg9IjIiIGhlaWdodD0iMTcwIiBzdHlsZT0iZmlsbDpibGFjayIgLz4KPHJlY3QgeD0iMzMwIiB5PSIyMCIgd2lkdGg9IjQiIGhlaWdodD0iMTcwIiBzdHlsZT0iZmlsbDpibGFjayIgLz4KPHJlY3QgeD0iMjAiIHk9IjIwMCIgd2lkdGg9IjM1MCIgaGVpZ2h0PSI5MCIgc3R5bGU9ImZpbGw6YmxhY2siIC8+Cjx0ZXh0IHg9IjMwIiB5PSIyNDAiIHN0eWxlPSJmaWxsOndoaXRlO2ZvbnQtc2l6ZTozMHB0O3RleHQtYW5jaG9yOnN0YXJ0IiA+MjMxNjQ4PC90ZXh0Pgo8dGV4dCB4PSIzNTAiIHk9IjI3MCIgc3R5bGU9ImZpbGw6d2hpdGU7Zm9udC1zaXplOjUwcHQ7dGV4dC1hbmNob3I6ZW5kIiA+OTc1MzwvdGV4dD4KPC9zdmc+Cg=="
     */
    private array $content = [];

    /**
     * Список идентификаторов сборочных заданий
     */
    private array $orders = [];

    /**
     * Ширина этикетки
     */
    private int $width = 40;

    /**
     * Высота этикетки
     */
    private int $height = 30;

    /**
     * Тип этикетки
     * "svg" "zplv" "zplh" "png"
     */
    private $type = 'svg';


    /**
     * Возвращает список этикеток по переданному массиву сборочных заданий.
     * Можно запросить этикетку в формате svg, zplv (вертикальный), zplh (горизонтальный), png.
     *
     * @see https://openapi.wildberries.ru/#tag/Marketplace-Sborochnye-zadaniya/paths/~1api~1v3~1orders~1stickers/post
     *
     */
    public function findByOrders(): self
    {
        if(empty($this->orders))
        {
            throw new InvalidArgumentException(
                'Не указан cписок идентификаторов сборочных заданий через вызов метода addOrder: ->addOrder(5632423)'
            );
        }

        $data = ["orders" => $this->orders];

        $response = $this->TokenHttpClient()->request(
            'POST',
            '/api/v3/orders/stickers?type='.$this->type.'&width='.$this->width.'&height='.$this->height,
            ['json' => $data],
        );

        if($response->getStatusCode() !== 200)
        {
            //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');

            $content = $response->toArray(false);
            
            throw new DomainException(
                message: $response->getStatusCode().': '.$content['message'] ?? self::class,
                code: $response->getStatusCode()
            );
        }
        $content = $response->toArray(false);
        $this->content = $content['stickers'];

        return $this;
    }


    public function getContent(): array
    {
        return $this->content;
    }


    /**
     * Добавить в список идентификатор сборочного задания
     */
    public function addOrder(int|string $order): void
    {
        $this->orders[] = $order;
    }


    /**
     * Устанавливает размер стикера 400x300
     */
    public function setSmallSize(): self
    {
        $this->width = 40;
        $this->height = 30;

        return $this;
    }


    /**
     * Устанавливает размер стикера 580x400
     */
    public function setBigSize(): self
    {
        $this->width = 58;
        $this->height = 40;

        return $this;
    }


    /**
     * Тип этикетки svg
     */
    public function setTypeSVG(): self
    {
        $this->type = 'svg';

        return $this;
    }


    /**
     * Тип этикетки zplv (вертикальный)
     */
    public function setTypeZPLV(): self
    {
        $this->type = 'zplv';

        return $this;
    }


    /**
     * Тип этикетки zplh (горизонтальный)
     */
    public function setTypeZPLH(): self
    {
        $this->type = 'zplh';

        return $this;
    }


    /**
     * Тип этикетки png
     */
    public function setTypePNG(): self
    {
        $this->type = 'png';

        return $this;
    }

}