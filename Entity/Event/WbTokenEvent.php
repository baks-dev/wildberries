<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
use BaksDev\Wildberries\Entity\Modify\WbTokenModify;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Event */

#[ORM\Entity]
#[ORM\Table(name: 'wb_token_event')]
#[ORM\Index(columns: ['profile', 'active'])]
class WbTokenEvent extends EntityEvent
{
    const TABLE = 'wb_token_event';

    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: WbTokenEventUid::TYPE)]
    private WbTokenEventUid $id;

    /**
     * ID настройки (профиль пользователя)
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profile;


    /**
     * Токен
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $token;

    /**
     * Cookie
     */
    #[ORM\OneToOne(targetEntity: WbTokenCookie::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbTokenCookie $cookie = null;

    /**
     * Статус true = активен / false = заблокирован
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: WbTokenModify::class, mappedBy: 'event', cascade: ['all'])]
    private WbTokenModify $modify;

    /**
     * Торговая наценка
     * строковое число (пример: 100|-100)
     * строковое число с процентом - процент (пример: 10%|-10%)
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $percent = null;


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
        return $this->profile;
    }
}