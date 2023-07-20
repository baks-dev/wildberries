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

namespace BaksDev\Wildberries\Api\Token\Supplies;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;

final class WildberriesSupplyInfo extends Wildberries
{

    /**
     * "id": "WB-GI-1234567" - Идентификатор поставки
     * "done": true - Флаг закрытия поставки
     * "createdAt": "2022-05-04T07:56:29Z" - Дата создания поставки (RFC3339)
     * "closedAt": "2022-05-04T07:56:29Z" - Дата закрытия поставки (RFC3339)
     * "scanDt": "2022-05-04T07:56:29Z" - Дата скана поставки (RFC3339)
     * "name": "Тестовая поставка" - Наименование поставки
     * "isLargeCargo": true - сКГТ-признак поставки
     */
    private array $content = [];

    /**
     * Идентификатор поставки
     * Example: WB-GI-1234567
     */
    private ?string $supply = null;


    public function withSupply(string $supply): self
    {
        $this->supply = $supply;

        return $this;
    }


    /**
     * Получить QR поставки
     * Возвращает QR в svg, zplv (вертикальный), zplh (горизонтальный), png.
     *
     * @see https://openapi.wildberries.ru/#tag/Marketplace-Sborochnye-zadaniya/paths/~1api~1v3~1supplies~1{supply}~1barcode/get
     *
     */
    public function getInfo(): self
    {
        if($this->supply === null)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор поставки через вызов метода withSupply: ->withSupply("WB-GI-1234567")'
            );
        }

        $response = $this->TokenHttpClient()->request(
            'GET',
            '/api/v3/supplies/'.$this->supply,
        );

        if($response->getStatusCode() !== 200)
        {
            $content = $response->toArray(false);
            //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');
            throw new DomainException(
                message: $response->getStatusCode().': '.$content['message'] ?? self::class,
                code: $response->getStatusCode()
            );
        }
        
        $content = $response->toArray(false);
        $this->content = $content;

        return $this;
    }


    public function getContent(): array
    {
        return $this->content;
    }
}