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

namespace BaksDev\Wildberries\Api\Token\Card\WildberriesCards;

use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\Warehouse;
use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use Generator;
use InvalidArgumentException;
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
        if($limit > 1000)
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
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Prosmotr/paths/~1content~1v1~1cards~1cursor~1list/post
     *
     */
    public function findAll(): Generator
    {
        if($this->test)
        {
            $content = $this->dataTest();
            $this->count = count($content["data"]["cards"]);

            foreach($content['data']['cards'] as $data)
            {
                yield new Card($this->getProfile(), $data);
            }

            return;
        }


        /** Кешируем результат запроса */

        $cache = new FilesystemAdapter('Wildberries');

        $content = $cache->get('cards-'.$this->profile->getValue().$this->limit.($this->nomenclature ?: '').($this->updated ?: ''), function(ItemInterface $item) {

            $item->expiresAfter(60 * 60);

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

                throw new DomainException(
                    message: $response->getStatusCode().': '.$content['errorText'] ?? self::class,
                    code: $response->getStatusCode()
                );
            }

            return $response->toArray(false);

        });

        /** Сохраняем курсоры для следующей итерации */
        $this->count = count($content["data"]["cards"]);
        $this->updated = $content['data']['cursor']['updatedAt'];
        $this->nomenclature = $content['data']['cursor']['nmID'];


        foreach($content['data']['cards'] as $data)
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


    public function dataTest(): array
    {
        return [
            "data" => [
                "cards" => [

                    /* Карточка БЕЗ множественного варианта */

                    [
                        "sizes" => [
                            [
                                "chrtID" => 301804600,
                                "techSize" => "0",
                                "skus" => [
                                    "2038811140828"
                                ]
                            ]
                        ],
                        "mediaFiles" => [
                            "https://basket-12.wb.ru/vol1830/part183060/183060059/images/big/1.jpg",
                            "https://basket-12.wb.ru/vol1830/part183060/183060059/images/big/2.jpg",
                        ],
                        "colors" => [
                            "белый"
                        ],
                        "updateAt" => "2023-10-19T13:56:21Z",
                        "vendorCode" => "ARTICLE-1111-01",
                        "brand" => "Brand 1",
                        "object" => "Кружки",
                        "nmID" => 183060059,
                        "imtID" => 166320490,
                        "objectID" => 812,
                        "isProhibited" => false,
                        "tags" => [
                        ]
                    ],


                    /* Карточка с множественными вариантами */

                    [
                        "sizes" => [
                            [
                                "chrtID" => 129702305,
                                "techSize" => "3XL",
                                "skus" => [
                                    "2038811140828"
                                ]
                            ]
                        ],
                        "mediaFiles" => [
                            "https://basket-05.wb.ru/vol774/part77484/77484221/images/big/1.jpg",
                            "https://basket-05.wb.ru/vol774/part77484/77484221/images/big/2.jpg",
                        ],
                        "colors" => [
                            "белый"
                        ],
                        "updateAt" => "2023-10-19T16:17:56Z",
                        "vendorCode" => "ARTICLE-2222-01",
                        "brand" => "Brand 2",
                        "object" => "Футболки",
                        "nmID" => 77484221,
                        "imtID" => 61111842,
                        "objectID" => 192,
                        "isProhibited" => false,
                        "tags" => [
                        ]
                    ]
                ],


                "cursor" => [
                    "updatedAt" => "2022-08-10T10:16:52Z",
                    "nmID" => 66964167,
                    "total" => 1
                ]
            ],
            "error" => false,
            "errorText" => "",
            "additionalErrors" => null
        ];
    }

}