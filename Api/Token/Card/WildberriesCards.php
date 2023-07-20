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

namespace BaksDev\Wildberries\Api\Token\Card;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;

final class WildberriesCards extends Wildberries
{

    /**
     * Лимит карточек
     */
    private ?int $limit = 100;

    /**
     * Список карточек товаров
     */
    private array $content = [];

    /**
     * Номер Артикула WB с которой надо запрашивать следующий список КТ
     */
    private ?int $nomenclature = null;

    /**
     * Дата с которой надо запрашивать следующий список КТ
     */
    private ?string $updated = null;


    /**
     * Лимит карточек
     */
    public function limit(int $limit): self
    {
        if($limit > 100)
        {
            throw new InvalidArgumentException(message: 'В параметр limit не должно присваиваться больше 100');
        }

        $this->limit = $limit;

        return $this;
    }


    /**
     * Номер Артикула WB с которой надо запрашивать следующий список КТ
     */
    public function nomenclature(?int $nomenclature): self
    {
        $this->nomenclature = $nomenclature;

        return $this;
    }


    public function updated(?string $updated): self
    {
        $this->updated = $updated;

        return $this;
    }


    /** Метод позволяет получить список созданных НМ по фильтру (баркод, артикул продавца, артикул WB (nmId), тег) с пагинацией и сортировкой.
     * ОГРАНИЧЕНИЕ! Допускается максимум 100 запросов в минуту на любые методы контента в целом.
     *
     * @see https://openapi.wildberries.ru/#tag/Kontent-Prosmotr/paths/~1content~1v1~1cards~1cursor~1list/post
     *
     */
    public function findAll(): self
    {
        $data = [
            "sort" => [
                'cursor' => [
                    "limit"     => $this->limit,

                    // Время обновления последней КТ из предыдущего ответа на запрос списка КТ.
                    "updatedAt" => $this->updated,

                    // Номенклатура последней КТ из предыдущего ответа на запрос списка КТ.
                    "nmID"      => $this->nomenclature,
                ],

                "filter" => [
                    //"textSearch" => $search,
                    "withPhoto" => 1,
                ],
            ],
        ];

        $response = $this->TokenHttpClient()
            ->request(
                'POST',
                '/content/v1/cards/cursor/list',
                ['json' => $data],
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
        $this->content = $content['data']['cards'];

        return $this;
    }


    public function getContent(): array
    {
        return $this->content;
    }


    /**
     * Limit
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

}