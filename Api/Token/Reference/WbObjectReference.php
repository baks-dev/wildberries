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

namespace BaksDev\Wildberries\Api\Token\Reference;

use BaksDev\Wildberries\Api\Wildberries;
use DomainException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WbObjectReference extends Wildberries
{

    /**
     * "objectName": "3D очки" - Название категории
     * "parentName": "Электроника" - Название родительской категории
     * "isVisible": true - Виден на сайте
     */
    private array $content;


    /**
     * Категория товаров
     *
     * С помощью данного метода можно получить список категорий товаров по текстовому фильтру (названию категории).
     *
     * @see https://openapi.wildberries.ru/#tag/Kontent-Konfigurator/paths/~1content~1v1~1object~1all/get
     */
    public function findObject(): self
    {
        $cache = new FilesystemAdapter();

        /**
         * Кешируем результат запроса
         *
         * @var  ResponseInterface $response
         */
        $response = $cache->get('wb_object_reference', function(ItemInterface $item)
            {
                $item->expiresAfter(60 * 60 * 24);

                return $this->TokenHttpClient()
                    ->request(
                        'GET',
                        '/content/v1/object/all',
                        ['query' => ['top' => 8000]],
                    );
            });

        if($response->getStatusCode() !== 200)
        {
            $content = $response->toArray(false);
            //$this->logger->critical('curl -X POST "' . $url . '" ' . $curlHeader . ' -d "' . $data . '"');
            throw new DomainException(
                message: $response->getStatusCode().': '.$content['errorText'] ?? self::class,
                code: $response->getStatusCode()
            );
        }

        $content = $response->toArray(false);
        $this->content = $content['data'];

        return $this;
    }


    /**
     * "objectName": "3D очки" - Название категории
     * "parentName": "Электроника" - Название родительской категории
     * "isVisible": true - Виден на сайте
     */
    public function getContent(): array
    {
        return $this->content;
    }

}