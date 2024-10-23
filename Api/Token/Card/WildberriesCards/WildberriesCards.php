<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Api\Token\Card\WildberriesCards;

use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\SellerWarehouse;
use BaksDev\Wildberries\Api\Wildberries;
use DateInterval;
use DomainException;
use Generator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;


final class WildberriesCards extends Wildberries
{

    private int $count = 0;

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
        $this->limit = $limit > 100 ? 100 : $limit;

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
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Prosmotr/paths/~1content~1v2~1get~1cards~1list/post
     *
     */
    public function findAll(): Generator
    {


        /** Кешируем результат запроса */

        $cache = new FilesystemAdapter('wildberries');

        $content = $cache->get('cards-'.$this->profile->getValue().$this->limit.($this->nomenclature ?: '').($this->updated ?: ''), function(
            ItemInterface $item
        ) {

            $item->expiresAfter(DateInterval::createFromDateString('1 hours'));

            $data = [
                "settings" => [
                    'cursor' => [
                        "limit" => $this->limit,

                        // Время обновления последней КТ из предыдущего ответа на запрос списка КТ.
                        "updatedAt" => $this->updated,

                        // Номенклатура последней КТ из предыдущего ответа на запрос списка КТ.
                        "nmID" => $this->nomenclature,
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
                    '/content/v2/get/cards/list',
                    ['json' => $data],
                );


            if($response->getStatusCode() !== 200)
            {

                $item->expiresAfter(DateInterval::createFromDateString('1 seconds'));

                $content = $response->toArray(false);

                throw new DomainException(
                    message: $response->getStatusCode().': '.$content['errorText'] ?? self::class,
                    code: $response->getStatusCode()
                );
            }

            return $response->toArray(false);

        });

        /** Сохраняем курсоры для следующей итерации */
        $this->count = count($content["cards"]);
        $this->updated = $content['cursor']['updatedAt'] ?? null;
        $this->nomenclature = $content['cursor']['nmID'] ?? null;

        foreach($content['cards'] as $data)
        {
            yield new Card($this->getProfile(), $data);
        }
    }


    /**
     * Limit
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Count
     */
    public function getCount(): int
    {
        return $this->count;
    }

}