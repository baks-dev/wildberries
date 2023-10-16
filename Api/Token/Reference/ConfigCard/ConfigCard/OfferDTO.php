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

namespace BaksDev\Wildberries\Api\Token\Reference\ConfigCard\ConfigCard;

final class OfferDTO
{
    private bool $available; //": true,
    private bool $required; //": true,
    private bool $onlyDictionary; //": true,
    private string $type; //": "Основной цвет",
    private ?string $dictionary = null; //": "/colors"
    
    /**
     * @param bool $isAvailable
     * @param bool $required
     * @param bool $useOnlyDictionaryValues
     * @param string $type
     * @param string|null $dictionary
     */
    public function __construct(
      array $data
    )
    {
        $this->available = $data['isAvailable'];
        $this->required = $data['required'];
        $this->onlyDictionary = $data['useOnlyDictionaryValues'];
        $this->type = $data['type'];
        $this->dictionary = $data['dictionary'];
    }
    
    /**
     * @return bool
     */
    public function isAvailable() : bool
    {
        return $this->available;
    }
    
    /**
     * @return bool
     */
    public function isRequired() : bool
    {
        return $this->required;
    }
    
    /**
     * @return bool
     */
    public function isOnlyDictionary() : bool
    {
        return $this->onlyDictionary;
    }
    

    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @return string|null
     */
    public function getDictionary() : ?string
    {
        return $this->dictionary;
    }
    
    
    
    
}