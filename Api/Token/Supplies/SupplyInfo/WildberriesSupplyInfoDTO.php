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

namespace BaksDev\Wildberries\Api\Token\Supplies\SupplyInfo;

use DateTimeImmutable;

final class WildberriesSupplyInfoDTO
{

    /**
     * Идентификатор поставки
     */
    private string $identifier;

    /**
     * Флаг закрытия поставки
     */
    private bool $done;

    /**
     * Дата создания поставки
     */
    private DateTimeImmutable $created;

    /**
     * Дата закрытия поставки
     */
    private ?DateTimeImmutable $closed;

    /**
     * Дата скана поставки
     */
    private ?DateTimeImmutable $scan;

    /**
     * Наименование поставки
     */
    private string $name;

    /**
     * сКГТ-признак поставки
     */
    private bool $cargo;

    /**
     * {
     *
     * "id": "WB-GI-1234567",
     * "done": true,
     * "createdAt": "2022-05-04T07:56:29Z",
     * "closedAt": "2022-05-04T07:56:29Z",
     * "scanDt": "2022-05-04T07:56:29Z",
     * "name": "Тестовая поставка",
     * "isLargeCargo": true
     *
     * }
     * */
    public function __construct(array $content)
    {

        /*Идентификатор поставки*/
        $this->identifier = $content['id'];

        /*Наименование поставки*/
        $this->name = $content['name'];

        /*Флаг закрытия поставки*/
        $this->done = $content['done'];

        /*Дата создания поставки (RFC3339)*/
        $this->created = new DateTimeImmutable($content['createdAt']);

        /*Дата закрытия поставки (RFC3339)*/
        $this->closed = $content['closedAt'] ? new DateTimeImmutable($content['closedAt']) : null;

        /*Дата скана поставки (RFC3339)*/
        $this->scan = $content['scanDt'] ? new DateTimeImmutable($content['scanDt']) : null;

        /*сКГТ-признак поставки*/
        $this->cargo = $content['isLargeCargo'] ?: false;
    }

    /**
     * Identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Done
     */
    public function isDone(): bool
    {
        return $this->done;
    }

    /**
     * Created
     */
    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * Closed
     */
    public function getClosed(): ?DateTimeImmutable
    {
        return $this->closed;
    }

    /**
     * Scan
     */
    public function getScan(): ?DateTimeImmutable
    {
        return $this->scan;
    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Cargo
     */
    public function isCargo(): bool
    {
        return $this->cargo;
    }

}