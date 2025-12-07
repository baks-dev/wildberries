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

declare(strict_types=1);

namespace BaksDev\Wildberries\Type\Authorization;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Cookie\WbTokenCookie;
use BaksDev\Wildberries\Type\Token\WbTokenString;
use Doctrine\DBAL\Types\Types;

/** @see WbTokenCookie */
final class WbAuthorizationToken
{

    /**
     * ID настройки (профиль пользователя)
     */
    private readonly string $profile;

    /**
     * Токен
     */
    private readonly string $token;


    /**
     * Торговая наценка
     * строковое число (пример: 100|-100)
     * строковое число с процентом - процент (пример: 10%|-10%)
     */
    private ?string $percent = null;

    private ?bool $card = null;

    private ?bool $stock = null;

    private ?string $warehouse = null;

    public function __construct(
        UserProfileUid|string $profile,
        string $token,
        ?string $warehouse = null,
        ?string $percent = null,
        ?bool $card = null,
        ?bool $stock = null
    )
    {
        $this->profile = (string) $profile;
        $this->token = $token;
        $this->percent = $percent;
        $this->card = $card;
        $this->stock = $stock;
        $this->warehouse = $warehouse;
    }


    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }


    public function getToken(): WbTokenString
    {
        return new WbTokenString($this->token);
    }

    public function getWarehouse(): ?string
    {
        return $this->warehouse;
    }

    public function getPercent(): ?string
    {
        return $this->percent;
    }

    public function isCard(): bool
    {
        return $this->card === true;
    }

    public function isStock(): ?bool
    {
        return $this->stock === true;
    }

}