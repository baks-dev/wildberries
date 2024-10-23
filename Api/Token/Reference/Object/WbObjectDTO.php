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

namespace BaksDev\Wildberries\Api\Token\Reference\Object;


final class WbObjectDTO
{

    /**
     * Идентификатор категории
     */
    private int $id;

    /**
     * Название категории
     */
    private string $name;


    /**
     * Идентификатор родительской категории
     */
    private int $parent;

    /**
     * Название родительской категории
     */
    private string $category;


    public function __construct(array $content)
    {

        $this->id = $content["subjectID"]; // => 2560,
        $this->name = $content["subjectName"]; // => "3D очки",

        $this->parent = $content["parentID"]; // => 479,
        $this->category = $content["parentName"]; // => "Электроника",

    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Parent
     */
    public function getParent(): int
    {
        return $this->parent;
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

}