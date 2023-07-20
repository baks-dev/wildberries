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
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class WildberriesCardByArticle extends Wildberries
{

    /**
     * Список карточек товаров
     */
    private array $content = [];

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


    /** Получение карточки товара по вендор кодам (артикулам)
     * ОГРАНИЧЕНИЕ! Допускается максимум 100 запросов в минуту на любые методы контента в целом.
     *
     * @see https://openapi.wildberries.ru/#tag/Kontent-Prosmotr/paths/~1content~1v1~1cards~1filter/post
     *
     */
    public function findByArticle(): self
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
        $this->content = $content['data'];

        return $this;
    }


    public function getContent(): array
    {
        return $this->content;
    }
}