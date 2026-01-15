<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\UseCase\Admin\NewEdit;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\WbTokenEventInterface;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Active\WbTokenActiveDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Card\WbTokenCardDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Orders\WbTokenOrdersDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Percent\WbTokenPercentDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Profile\WbTokenProfileDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Sales\WbTokenSalesDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Stocks\WbTokenStockDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Token\WbTokenValueDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Warehouse\WbTokenWarehouseDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** @see MegamarketTokenEvent */
final class WbTokenDTO implements WbTokenEventInterface
{

    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?WbTokenEventUid $id = null;

    /**
     * Статус true = активен / false = заблокирован
     */
    #[Assert\Valid]
    private WbTokenActiveDTO $active;

    /**
     * Профиль пользователя
     */
    #[Assert\Valid]
    private WbTokenProfileDTO $profile;

    /**
     * Токен
     */
    #[Assert\Valid]
    private WbTokenValueDTO $token;

    /**
     * Торговая наценка
     */
    #[Assert\Valid]
    private WbTokenPercentDTO $percent;

    /**
     * Обновлять карточки
     */
    #[Assert\Valid]
    private WbTokenCardDTO $card;

    /**
     * Обновлять остатки
     */
    #[Assert\Valid]
    private WbTokenStockDTO $stock;

    /**
     * Обновление заказов
     */
    #[Assert\Valid]
    private WbTokenOrdersDTO $orders;

    /**
     * Идентификатор склада
     */
    #[Assert\Valid]
    private WbTokenWarehouseDTO $warehouse;

    /** Запустить/остановить продажи */
    #[Assert\Valid]
    private WbTokenSalesDTO $sales;


    public function __construct()
    {
        $this->active = new WbTokenActiveDTO();
        $this->profile = new WbTokenProfileDTO();
        $this->token = new WbTokenValueDTO();
        $this->percent = new WbTokenPercentDTO();
        $this->card = new WbTokenCardDTO();
        $this->stock = new WbTokenStockDTO();
        $this->orders = new WbTokenOrdersDTO();
        $this->warehouse = new WbTokenWarehouseDTO();
        $this->sales = new WbTokenSalesDTO();
    }

    public function setId(?WbTokenEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?WbTokenEventUid
    {
        return $this->id;
    }

    public function getId(): ?WbTokenEventUid
    {
        return $this->id;
    }

    public function getActive(): WbTokenActiveDTO
    {
        return $this->active;
    }

    public function getProfile(): WbTokenProfileDTO
    {
        return $this->profile;
    }

    public function getToken(): WbTokenValueDTO
    {
        return $this->token;
    }

    public function getPercent(): WbTokenPercentDTO
    {
        return $this->percent;
    }

    public function getCard(): WbTokenCardDTO
    {
        return $this->card;
    }

    public function getStock(): WbTokenStockDTO
    {
        return $this->stock;
    }

    public function getWarehouse(): WbTokenWarehouseDTO
    {
        return $this->warehouse;
    }

    public function getOrders(): WbTokenOrdersDTO
    {
        return $this->orders;
    }

    public function getSales(): WbTokenSalesDTO
    {
        return $this->sales;
    }
}