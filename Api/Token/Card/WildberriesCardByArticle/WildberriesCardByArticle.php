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

namespace BaksDev\Wildberries\Api\Token\Card\WildberriesCardByArticle;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class WildberriesCardByArticle extends Wildberries
{

    private ?array $article = null;


    /**
     * Добавить в список идентификатор сборочного задания
     */
    public function addArticle(string $article): self
    {
        $this->article[] = $article;

        return $this;
    }


    public function resetArticle(): self
    {
        $this->article = null;

        return $this;
    }


    public function withArticle(?array $article): self
    {
        $this->article = $article;

        return $this;
    }


    /** Получение КТ по артикулам продавца
     * ОГРАНИЧЕНИЕ! Допускается максимум 100 запросов в минуту на любые методы контента в целом.
     *
     * Метод позволяет получить полную информацию по КТ с помощью артикула(-ов) продавца.
     *
     * Важно! Карточки, находящиеся в корзине, в ответе метода не выдаются.
     * Получить такие карточки можно только методом "Список НМ, находящихся в корзине".
     * Поиск работает только по полному совпадению значений. Получить точные значения для поиска можно методом "Список НМ".
     *
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Prosmotr/paths/~1content~1v1~1cards~1filter/post
     *
     */
    public function findByArticle(): Generator
    {
        if(empty($this->article))
        {
            throw new InvalidArgumentException(
                'Не указан артикул или массив артикулов через вызов метода addArticle: ->addArticle(5632423)'
            );
        }

        if(count($this->article) > 100)
        {
            throw new InvalidArgumentException(
                'В параметре article не должно быть больше 100 артикулов'
            );
        }


        if($this->test)
        {
            $content = $this->dataTest();

            foreach($content['data'] as $data)
            {
                yield new CardByArticle($data);
            }
        }


        $key = md5(json_encode($this->article));

        /** Кешируем результат запроса */
        $cache = new FilesystemAdapter('Wildberries');

        $response = $cache->get($key, function(ItemInterface $item)
            {
                $item->expiresAfter(60 * 60);

                $data = ["vendorCodes" => $this->article];

                $response = $this->TokenHttpClient()
                    ->request(
                        'POST',
                        '/content/v1/cards/filter',
                        ['json' => $data],
                    );

                if($response->getStatusCode() !== 200)
                {
                    $content = $response->toArray(false);

                    //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');
                    throw new DomainException(
                        message: $response->getStatusCode().': '.($content['errorText'] ?? self::class),
                        code: $response->getStatusCode()
                    );
                }


                return $response;

            });


        $content = $response->toArray(false);

        foreach($content['data'] as $data)
        {
            yield new CardByArticle($data);
        }
    }


    public function dataTest()
    {
        return [
            "data" => [
                [
                    "imtID" => 9876545135,
                    "object" => "Рубашки рабочие",
                    "objectID" => 6165,
                    "nmID" => 7986515498,
                    "vendorCode" => "vendorCode",
                    "mediaFiles" => [
                        "URLphoto",
                        "URLvideo"
                    ],
                    "sizes" => [
                        [
                            "techSize" => "50-52",
                            "skus" => [
                                "9845613215468"
                            ],
                            "chrtID" => 87654235466,
                            "wbSize" => "50-52",
                            "price" => 10500
                        ],
                        [
                            "techSize" => "54-56",
                            "skus" => [
                                "9876513216546"
                            ],
                            "chrtID" => 651898613,
                            "wbSize" => "54-56",
                            "price" => 10500
                        ],
                        [
                            "techSize" => "46-48",
                            "skus" => [
                                "984532321"
                            ],
                            "chrtID" => 5465646,
                            "wbSize" => "46-48",
                            "price" => 10500
                        ],
                        [
                            "techSize" => "42-44",
                            "skus" => [
                                "123123123"
                            ],
                            "chrtID" => 12313213,
                            "wbSize" => "42-44",
                            "price" => 10500
                        ]
                    ],
                    "characteristics" => [
                        [
                            "Ширина упаковки" => 25
                        ],
                        [
                            "Высота упаковки" => 5
                        ],
                        [
                            "Длина упаковки" => 30
                        ],
                        [
                            "Особенности модели" => [
                                "дышащий материал"
                            ]
                        ],
                        [
                            "Тип ростовки" => [
                                "для среднего роста"
                            ]
                        ],
                        [
                            "Дата регистрации сертификата/декларации" => [
                                "23.12.2019"
                            ]
                        ],
                        [
                            "Вид застежки" => [
                                "без застежки"
                            ]
                        ],
                        [
                            "Покрой" => [
                                "полуприлегающий"
                            ]
                        ],
                        [
                            "Рисунок" => [
                                "апельсины"
                            ]
                        ],
                        [
                            "Уход за вещами" => [
                                "стирка при t не более 30°C"
                            ]
                        ],
                        [
                            "Возрастная группа(лет)" => [
                                "Женщины девушки"
                            ]
                        ],
                        [
                            "Цвет" => [
                                "оранжевый"
                            ]
                        ],
                        [
                            "ТНВЭД" => [
                                "6104620000"
                            ]
                        ],
                        [
                            "Ставка НДС" => [
                                "20"
                            ]
                        ],
                        [
                            "Тип рукава" => [
                                "без рукавов"
                            ]
                        ],
                        [
                            "Утеплитель" => [
                                "без утепления"
                            ]
                        ],
                        [
                            "Назначение" => [
                                "домашняя одежда",
                                "повседневная",
                                "пляж"
                            ]
                        ],
                        [
                            "Модель брюк" => [
                                "свободные"
                            ]
                        ],
                        [
                            "Модель комбинезона" => [
                                "домашний"
                            ]
                        ],
                        [
                            "Фактура материала" => [
                                "гладкий"
                            ]
                        ],
                        [
                            "Пол" => [
                                "Женский"
                            ]
                        ],
                        [
                            "Размер на модели" => [
                                "42-44"
                            ]
                        ],
                        [
                            "Комплектация" => [
                                "комбинезон-1шт."
                            ]
                        ],
                        [
                            "Дата окончания действия сертификата/декларации" => [
                                "22.12.2029"
                            ]
                        ],
                        [
                            "Коллекция" => [
                                "Весна-Лето 2023"
                            ]
                        ],
                        [
                            "Бренд" => "Бренд"
                        ],
                        [
                            "Состав" => [
                                "92% хлопок,8% эластан"
                            ]
                        ],
                        [
                            "Номер декларации соответствия" => [
                                "Номер декларации"
                            ]
                        ],
                        [
                            "Предмет" => "Комбинезоны"
                        ],
                        [
                            "Наименование" => "Комбинезон пижамный домашний женский"
                        ],
                        [
                            "Описание" => "Комбинезон на бретелях свободного объема с фигурным вырезом на спине."
                        ]
                    ],
                    "isProhibited" => false,
                    "tags" => [
                        [
                            "id" => 374407,
                            "name" => "многоразовый",
                            "color" => "FFECC7"
                        ]
                    ]
                ]
            ],
            "error" => false,
            "errorText" => "",
            "additionalErrors" => null
        ];
    }

}