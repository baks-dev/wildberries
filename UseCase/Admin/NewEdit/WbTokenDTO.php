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

namespace BaksDev\Wildberries\UseCase\Admin\NewEdit;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\WbTokenEventInterface;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see YaMarketTokenEvent */
final class WbTokenDTO implements WbTokenEventInterface
{

    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?WbTokenEventUid $id = null;

    /**
     * ID настройки (профиль пользователя)
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?UserProfileUid $profile = null;


    /**
     * Токен
     */
    #[Assert\NotBlank]
    private string $token;


    /**
     * Статус true = активен / false = заблокирован
     */
    private bool $active = true;

    private Cookie\WbTokenCookieDTO $cookie;


    public function __construct()
    {
        $this->cookie = new Cookie\WbTokenCookieDTO();
    }


    public function setId(?WbTokenEventUid $id): void
    {
        $this->id = $id;
    }


    public function getEvent(): ?WbTokenEventUid
    {
        return $this->id;
    }


    /**
     * Profile
     */
    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }


    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * Token
     */
    public function getToken(): string
    {
        return $this->token;
    }


    public function setToken(string $token): void
    {
        $this->token = $token;
    }


    /**
     * Active
     */
    public function getActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): void
    {
        $this->active = $active;
    }


    /**
     * Cookie
     */
    public function getCookie(): Cookie\WbTokenCookieDTO
    {
        return $this->cookie;
    }


    public function setCookie(Cookie\WbTokenCookieDTO $cookie): void
    {
        $this->cookie = $cookie;
    }

}