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

namespace BaksDev\Wildberries\Api\Token\Reference\ConfigCard;

use App\Module\Wildberries\Rest\Auth\WbTokenAuth;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ConfigCard implements ConfigCardInterface
{
    private HttpClientInterface $httpClient;
    private Serializer $serializer;

    public function __construct(WbTokenAuth $wbTokenAuth)
    {
        $this->httpClient = $wbTokenAuth->auth();


        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);

    }


    public function get(string $name)
    {
        $cache = new FilesystemAdapter('wildberries');

        /* Кешируем результат запроса */
        $response = $cache->get('wb_config_card_'.md5($name), function(ItemInterface $item) use ($name) {
            $item->expiresAfter(86400); // 3600 = 1 час / 86400 - сутки

            $retry = 1;

            while(true)
            {
                $response = $this->httpClient->request(
                    'GET',
                    '/api/v1/config/get/object/translated',
                    ['query' => ['name' => $name],]
                );

                if($response->getStatusCode() !== 200)
                {
                    ++$retry;
                }

                if($retry >= 5 || $response->getStatusCode() === 200)
                {
                    break;
                }
            }

            return $response;
        });

        $content = $response->toArray(false);

        if($response->getStatusCode() === 200 && $content['error'] === false && !empty($content['data']))
        {
            return new ConfigCardDTO($content['data']);
        }


        //$cache->delete('wb_config_card_'.md5($name));

        return false;

    }

}