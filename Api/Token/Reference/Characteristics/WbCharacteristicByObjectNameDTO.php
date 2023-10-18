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

namespace BaksDev\Wildberries\Api\Token\Reference\Characteristics;

final class WbCharacteristicByObjectNameDTO
{
    /**
     * Наименование подкатегории
     */

    private string $category;

    /**
     * Наименование характеристики
     */
    private string $name;

    /**
     * Характеристика обязательна к заполнению
     */
    private bool $required;

    /**
     * Единица измерения (см, гр и т.д.)
     */
    private string $unit;

    /**
     * Максимальное кол-во значений которое можно присвоить данной характеристике
     */
    private int $count;

    /**
     * Характеристика популярна у пользователей
     */
    private bool $popular;

    /**
     * Тип характеристики (1 и 0 - строка или массив строк; 4 - число или массив чисел)
     */
    private mixed $type;

    public function __construct(array $content)
    {
        $this->category = $content['objectName'];
        $this->name = $content['name'];
        $this->required = $content['required'];
        $this->unit = $content['unitName'];
        $this->count = $content['maxCount'];
        $this->popular = $content['popular'];
        $this->type = $content['charcType'];
    }

    /**
     * Category
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Required
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Unit
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * Count
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Popular
     */
    public function isPopular(): bool
    {
        return $this->popular;
    }

    /**
     * Type
     */
    public function getType(): mixed
    {
        return $this->type;
    }


}