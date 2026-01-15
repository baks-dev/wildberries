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

namespace BaksDev\Wildberries\Entity\Event;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Access\WbTokenAccess;
use BaksDev\Wildberries\Entity\Cookie\WbTokenCookie;
use BaksDev\Wildberries\Entity\Event\Active\WbTokenActive;
use BaksDev\Wildberries\Entity\Event\Card\WbTokenCard;
use BaksDev\Wildberries\Entity\Event\Modify\WbTokenModify;
use BaksDev\Wildberries\Entity\Event\Orders\WbTokenOrders;
use BaksDev\Wildberries\Entity\Event\Percent\WbTokenPercent;
use BaksDev\Wildberries\Entity\Event\Profile\WbTokenProfile;
use BaksDev\Wildberries\Entity\Event\Sales\WbTokenSales;
use BaksDev\Wildberries\Entity\Event\Stocks\WbTokenStocks;
use BaksDev\Wildberries\Entity\Event\Token\WbTokenValue;
use BaksDev\Wildberries\Entity\Event\Warehouse\WbTokenWarehouse;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Event */

#[ORM\Entity]
#[ORM\Table(name: 'wb_token_event')]
class WbTokenEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: WbTokenEventUid::TYPE)]
    private WbTokenEventUid $id;

    /**
     * Идентификатор Main
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: WbTokenUid::TYPE)]
    private WbTokenUid $main;

    /**
     * ID профиля пользователя
     */
    #[ORM\OneToOne(targetEntity: WbTokenProfile::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenProfile $profile = null;

    /** WbTokenValue */
    #[ORM\OneToOne(targetEntity: WbTokenValue::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenValue $token = null;

    /** WbTokenActive */
    #[ORM\OneToOne(targetEntity: WbTokenActive::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenActive $active = null;

    /** WbTokenCard */
    #[ORM\OneToOne(targetEntity: WbTokenCard::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenCard $card = null;

    /** WbTokenStocks */
    #[ORM\OneToOne(targetEntity: WbTokenStocks::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenStocks $stock = null;

    /** WbTokenOrders */
    #[ORM\OneToOne(targetEntity: WbTokenOrders::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenOrders $orders = null;

    /** WbTokenOrders */
    #[ORM\OneToOne(targetEntity: WbTokenSales::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenSales $sales = null;

    /** WbTokenPercent */
    #[ORM\OneToOne(targetEntity: WbTokenPercent::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenPercent $percent = null;

    /** WbTokenWarehouse */
    #[ORM\OneToOne(targetEntity: WbTokenWarehouse::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenWarehouse $warehouse = null;

    /** Модификатор */
    #[ORM\OneToOne(targetEntity: WbTokenModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private WbTokenModify $modify;

    public function __construct()
    {
        $this->id = new WbTokenEventUid();
        $this->modify = new WbTokenModify($this);
    }

    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getMain(): WbTokenUid
    {
        return $this->main;
    }

    public function setMain(WbToken|WbTokenUid $main): self
    {
        $this->main = $main instanceof WbToken ? $main->getId() : $main;
        return $this;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof WbTokenEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof WbTokenEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getId(): WbTokenEventUid
    {
        return $this->id;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile->getValue();
    }

    public function getPercent(): ?string
    {
        return $this->percent->getValue();
    }
}