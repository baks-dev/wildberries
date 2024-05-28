<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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
    
    public function __construct(WbTokenAuth $wbTokenAuth) {
        $this->httpClient = $wbTokenAuth->auth();

        
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
    
        $this->serializer = new Serializer($normalizers, $encoders);
        
    }
    
    
    public function get(string $name)
    {
        $cache = new FilesystemAdapter('wildberries');
    
        /* Кешируем результат запроса */
        $response = $cache->get('wb_config_card_'.md5($name), function (ItemInterface $item) use ($name)
        {
            $item->expiresAfter(86400); // 3600 = 1 час / 86400 - сутки
    
            $retry = 1;
    
            while(true)
            {
                $response = $this->httpClient->request(
                  'GET',
                  '/api/v1/config/get/object/translated',
                  [ 'query' => ['name' => $name], ]
                );
        
                if($response->getStatusCode() !== 200)
                {
                    ++$retry;
                }
        
                if($retry >= 5 || $response->getStatusCode() === 200) { break; }
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