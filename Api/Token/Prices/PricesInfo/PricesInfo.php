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

namespace BaksDev\Wildberries\Api\Token\Prices\PricesInfo;

use ArrayObject;
use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class PricesInfo extends Wildberries
{
    private ArrayObject $prices;

    /**
     * Получение информации о ценах.
     *
     * Получение информации по номенклатурам, их ценам, скидкам и промокодам. Если не указывать фильтры, вернётся весь товар.
     *
     * @see https://openapi.wildberries.ru/prices/api/ru/#tag/Ceny/paths/~1public~1api~1v1~1info/get
     *
     */
    public function prices(): self
    {

        if(!$this->profile)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }


//        if($this->test)
//        {
//            $content = $this->dataTest();
//
//            foreach($content as $data)
//            {
//                yield new Price($data);
//            }
//
//            return;
//        }


        /** Кешируем результат запроса */

        $cache = new FilesystemAdapter('Wildberries');

        $content = $cache->get('prices-'.$this->profile->getValue(), function(ItemInterface $item) {

            $item->expiresAfter(60 * 60 * 24);

            $response = $this->TokenHttpClient()->request(
                'GET',
                '/public/api/v1/info',
            );

            if($response->getStatusCode() !== 200)
            {
                $content = $response->toArray(false);

                throw new DomainException(
                    message: $content['message'] ?? self::class, code: $response->getStatusCode()
                );
            }

            return $response->toArray(false);

        });

        $this->prices = new ArrayObject();

        foreach($content as $data)
        {
            $this->prices->offsetSet($data['nmId'], new Price($data));
        }

        return $this;
    }

    public function getPriceByNomenclature(int $nomenclature): ?Price
    {
        if($this->prices->offsetExists($nomenclature))
        {
            return $this->prices->offsetGet($nomenclature);
        }

        return null;
    }

    public function dataTest(): array
    {
        return [
            "nmId" => 1234567,
            "price" => 1000,
            "discount" => 10,
            "promoCode" => 5
        ];
    }

}