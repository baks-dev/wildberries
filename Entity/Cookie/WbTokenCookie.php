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

namespace BaksDev\Wildberries\Entity\Cookie;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'wb_token_cookie')]
class WbTokenCookie extends EntityEvent
{

    public const TABLE = 'wb_token_cookie';

    /**
     * ID события
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'cookie', targetEntity: WbTokenEvent::class)]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private WbTokenEvent $event;

    /**
     * Идентификатор магазина (x-supplier-id)
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private ?string $identifier;

    /**
     * Токен (WBToken)
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private ?string $token;

    
    public function __construct(WbTokenEvent $event)
    {
        $this->event = $event;
    }


    public function getDto($dto): mixed
    {
        if($dto instanceof WbTokenCookieInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof WbTokenCookieInterface)
        {
            if(empty($dto->getToken()) || empty($dto->getIdentifier()))
            {
                return false;
            }

            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}