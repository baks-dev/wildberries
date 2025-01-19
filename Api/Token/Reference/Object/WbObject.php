<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Api\Token\Reference\Object;

use BaksDev\Wildberries\Api\Wildberries;
use DateInterval;
use Generator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WbObject extends Wildberries
{
    /**
     * Категория товаров
     *
     * С помощью данного метода можно получить список категорий товаров по текстовому фильтру (названию категории).
     *
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Konfigurator/paths/~1content~1v2~1object~1all/get
     */
    public function findObject(): Generator|false
    {
        $cache = new FilesystemAdapter('wildberries');

        /**
         * Кешируем результат запроса
         *
         * @var  ResponseInterface $response
         */
        $response = $cache->get('wb_object_reference', function(ItemInterface $item) {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->TokenHttpClient()
                ->request(
                    'GET',
                    '/content/v2/object/all',
                    ['query' => [
                        'limit' => 1000,
                        'locale' => 'ru'
                    ]],
                );
        });

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical('wildberries: ', $content);
            return false;
        }

        $content = $response->toArray(false);

        foreach($content['data'] as $data)
        {
            yield new WbObjectDTO($data);
        }
    }
}