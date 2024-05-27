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

namespace BaksDev\Wildberries\Api\Token\Warehouse\WildberriesWarehouses;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class WildberriesWarehouses extends Wildberries
{

    /**
     * Получить список складов WB
     *
     * @see https://openapi.wildberries.ru/marketplace/api/ru/#tag/Sklady/paths/~1api~1v3~1offices/get
     *
     */
    public function warehouses(): Generator
    {

        if(!$this->profile)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }


        if($this->test)
        {
            $content = $this->dataTest();

            foreach($content as $data)
            {
                yield new Warehouse($data);
            }

            return;
        }


        /** Кешируем результат запроса */

        $cache = new FilesystemAdapter('Wildberries');

        $content = $cache->get('warehouses-wildberries-'.$this->profile->getValue(), function(ItemInterface $item) {
            $item->expiresAfter(60 * 60);

            $response = $this->TokenHttpClient()->request(
                'GET',
                '/api/v3/offices',
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

        foreach($content as $data)
        {
            yield new Warehouse($data);
        }
    }

    public function dataTest(): array
    {
        return [
            [
                "address" => "ул. Троицкая, Подольск, Московская обл.",
                "name" => "Москва (Подольск)",
                "city" => "Москва",
                "id" => 15,
                "longitude" => 55.386871,
                "latitude" => 37.588898,
                "selected" => true
            ],

            [
                "address" => "ул. Троицкая, Химки, Московская обл.",
                "name" => "Москва (Химки)",
                "city" => "Москва",
                "id" => 18,
                "longitude" => 55.386872,
                "latitude" => 37.588899,
                "selected" => true
            ]
        ];
    }

}