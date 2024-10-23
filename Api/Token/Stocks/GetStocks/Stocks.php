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

declare(strict_types=1);

namespace BaksDev\Wildberries\Api\Token\Stocks\GetStocks;

use ArrayObject;

final class Stocks
{
    private ArrayObject $collection;

    public function __construct(array $content)
    {
        $this->collection = new ArrayObject();

        foreach($content['stocks'] as $data)
        {
            /** Если имеется остаток штрихкода, и он больше - не присваиваем*/
            if($this->collection->offsetExists($data['sku']))
            {
                $amount = $this->getAmount($data['sku']);

                if($amount > $data['amount'])
                {
                    return;
                }
            }

            $this->collection->offsetSet($data['sku'], $data['amount']);
        }
    }

    public function getAmount(int|string $barcode): int
    {
        if($this->collection->offsetExists($barcode))
        {
            return $this->collection->offsetGet($barcode);
        }

        return 0;
    }

}