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

namespace BaksDev\Wildberries\Api\Token\Reference\Characteristics;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WbCharacteristicByObjectName extends Wildberries
{

    /**
     * Поиск по родительской категории.
     */
    private ?string $name = null;


    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }


    /**
     * Характеристики для создания КТ по всем подкатегориям
     *
     * @see https://openapi.wildberries.ru/#tag/Kontent-Konfigurator/paths/~1content~1v1~1object~1characteristics~1list~1filter/get
     *
     * С помощью данного метода можно получить список характеристик, которые можно или нужно заполнить при создании КТ
     *     в подкатегории определенной родительской категории.
     */
    public function findCharacteristics(): Generator
    {

        if(!$this->name)
        {
            throw new InvalidArgumentException(
                'Не указано название родительской категории: ->name("Косухи")'
            );
        }

//        if($this->test)
//        {
//            $content = $this->dataTest();
//
//            foreach($content['data'] as $data)
//            {
//                yield new WbCharacteristicByObjectNameDTO($data);
//            }
//
//            return;
//        }

        $cache = new FilesystemAdapter('wildberries');

        /**
         * Кешируем результат запроса
         *
         * @var  ResponseInterface $response
         */
        $response = $cache->get('wb_config_card_'.md5($this->name), function(ItemInterface $item) {
            $item->expiresAfter(60 * 60 * 24);

            $response = $this->TokenHttpClient()
                ->request(
                    'GET',
                    '/content/v1/object/characteristics/'.$this->name,
                );

            return $response;
        });

        if($response->getStatusCode() !== 200)
        {
            $content = $response->toArray(false);

            throw new DomainException(
                message: $response->getStatusCode().': '.$content['errorText'] ?? self::class,
                code: $response->getStatusCode()
            );
        }

        $content = $response->toArray(false);

        if(empty($content['data']))
        {
            $cache->delete('wb_config_card_'.md5($this->name));
        }


        foreach($content['data'] as $data)
        {
            yield new WbCharacteristicByObjectNameDTO($data);
        }

    }

}