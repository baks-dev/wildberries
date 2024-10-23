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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @return ArrayCollection
     */
    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }

    /**
     * @return ArrayCollection
     */
    public function getOffer(): ArrayCollection
    {
        return $this->offer;
    }

    /**
     * @return ArrayCollection
     */
    public function getVariation(): ArrayCollection
    {
        return $this->variation;
    }

}