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

namespace BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses;

final class ProfileWarehouseDTO
{

    /** ID склада продавца */
    private int $id;

    /** ID склада WB */
    private int $office;

    /** Название склада продавца */
    private string $name;

    /** Тип товара, который принимает склад:
     *
     * 1 - обычный
     * 2 - СГТ (Сверхгабаритный товар)
     * 3 - КГТ (Крупногабаритный товар). Не используется на данный момент.
     */
    private int $category;

    /** Тип доставки, который принимает склад:
     *
     * 1 - доставка на склад Wildberries
     * 2 - доставка силами продавца
     * 3 - доставка курьером WB
     */
    private int $delivery;


    public function __construct(array $content)
    {
        $this->id = $content['id'];
        $this->office = $content['officeId'];
        $this->name = $content['name'];

        $this->category = $content['cargoType'];
        $this->delivery = $content['deliveryType'];

    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Office
     */
    public function getOffice(): int
    {
        return $this->office;
    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Category
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * Delivery
     */
    public function getDelivery(): int
    {
        return $this->delivery;
    }
}