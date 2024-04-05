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

declare(strict_types=1);

namespace BaksDev\Wildberries\Api\Token\Warehouse\WarehousesWildberries;

final class WildberriesWarehouseDTO
{

    /** ID*/
    private int $id;


    /** Адрес*/
    private string $address;


    /** Название*/
    private string $name;


    /** Город*/
    private string $city;


    /** Долгота*/
    private float $longitude;


    /*Широта*/
    private float $latitude;


    /**  Признак того, что склад уже выбран продавцом*/
    private bool $selected;


    /**
     * Тип товара, который принимает склад:
     *
     * 1 - обычный
     * 2 - СГТ (Сверхгабаритный товар)
     * 3 - КГТ (Крупногабаритный товар). Не используется на данный момент.
     */
    private int $cargo;

    /**
     *
     * Тип доставки, который принимает склад:
     *
     * 1 - доставка на склад Wildberries
     * 2 - доставка силами продавца
     * 3 - доставка курьером WB
     */
    private int $delivery;


    public function __construct(array $content)
    {
        $this->id = $content['id'];
        $this->address = $content['address'];
        $this->name = $content['name'];
        $this->city = $content['city'];
        $this->longitude = $content['longitude'];
        $this->latitude = $content['latitude'];
        $this->selected = $content['selected'];
        $this->cargo = $content['cargoType'];
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
     * Address
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * City
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Longitude
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Latitude
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Selected
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Cargo
     */
    public function getCargo(): int
    {
        return $this->cargo;
    }

    /**
     * Delivery
     */
    public function getDelivery(): int
    {
        return $this->delivery;
    }
}