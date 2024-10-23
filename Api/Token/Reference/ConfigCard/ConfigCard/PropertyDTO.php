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

namespace BaksDev\Wildberries\Api\Token\Reference\ConfigCard\ConfigCard;

final class PropertyDTO
{
    private bool $available;
    private bool $required;
    private bool $onlyDictionary;
    private string $type;
    private ?string $dictionary = null;
    private ?int $maxCount = null;
    private bool $isNumber;
    private ?string $units = null;


    public function __construct(
        array $data
    )
    {

        $this->available = $data['isAvailable'];
        $this->required = $data['required'];
        $this->onlyDictionary = $data['useOnlyDictionaryValues'];
        $this->type = $data['type'];
        $this->dictionary = $data['dictionary'] ?? null;
        $this->maxCount = $data['maxCount'] ?? null;
        $this->isNumber = $data['isNumber'] ?? false;
        $this->units = isset($data['units']) ? end($data['units']) : null;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isOnlyDictionary(): bool
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
    public function getDictionary(): ?string
    {
        return $this->dictionary;
    }

    /**
     * @return int|null
     */
    public function getMaxCount(): ?int
    {
        return $this->maxCount;
    }

    /**
     * @return bool
     */
    public function isNumber(): bool
    {
        return $this->isNumber;
    }

    /**
     * @return int|null
     */
    public function getUnits(): ?string
    {
        return $this->units;
    }

}