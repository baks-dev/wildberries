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

use BaksDev\Wildberries\Api\Token\Reference\ConfigCard\ConfigCard\OfferDTO;
use BaksDev\Wildberries\Api\Token\Reference\ConfigCard\ConfigCard\PropertyDTO;
use BaksDev\Wildberries\Api\Token\Reference\ConfigCard\ConfigCard\VariationDTO;
use Doctrine\Common\Collections\ArrayCollection;

final class ConfigCardDTO
{
    private string $name;
    
    private string $parent;
    
    private ArrayCollection $property;
    private ArrayCollection $offer;
    private ArrayCollection $variation;
    
    /**
     * @param ArrayCollection $addin
     * @param ArrayCollection $offer
     * @param ArrayCollection $variation
     */
    public function __construct(array $data)
    {
        $this->property = new ArrayCollection();
        $this->offer = new ArrayCollection();
        $this->variation = new ArrayCollection();
        
        
        $this->name = $data['name'];
        $this->parent = $data['parent'];
    
        foreach($data['addin'] as $prop)
        {
            $Property = new PropertyDTO($prop);
            $this->property->add($Property);
        }
       
        foreach($data['nomenclature']['variation']['addin'] as $var)
        {
            $Property = new VariationDTO($var);
            $this->variation->add($Property);
        }
        
        
        foreach($data['nomenclature']['addin'] as $addin)
        {
            $Property = new OfferDTO($addin);
            $this->offer->add($Property);
        }
        
    }
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getParent() : string
    {
        return $this->parent;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getProperty() : ArrayCollection
    {
        return $this->property;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getOffer() : ArrayCollection
    {
        return $this->offer;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getVariation() : ArrayCollection
    {
        return $this->variation;
    }
 
}