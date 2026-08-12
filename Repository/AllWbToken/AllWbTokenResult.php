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
 *
 */

declare(strict_types=1);

namespace BaksDev\Wildberries\Repository\AllWbToken;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use DateTimeImmutable;

final readonly class AllWbTokenResult
{
    public function __construct(
        private string $id,
        private string $event,
        private string $modify,

        private ?bool $active,
        private ?bool $card,
        private ?bool $stocks,
        private ?bool $orders,
        private ?bool $sales,

        private string $users_profile_event,
        private ?string $users_profile_status,
        private ?string $users_profile_username,
        private ?string $users_profile_avatar,
        private ?string $users_profile_avatar_ext,
        private ?bool $users_profile_avatar_cdn,
        private ?string $account_email,
        private ?string $account_status,

        private ?string $name = null,
    ) {}

    public function getId(): WbTokenUid
    {
        return new WbTokenUid($this->id);
    }

    public function getEvent(): WbTokenEventUid
    {
        return new WbTokenEventUid($this->event);
    }

    public function getModify(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->modify);
    }

    public function isActive(): bool
    {
        return $this->active === true;
    }

    public function isCard(): bool
    {
        return $this->card === true;
    }

    public function isStocks(): bool
    {
        return $this->stocks === true;
    }

    public function isOrders(): bool
    {
        return $this->orders === true;
    }

    public function isSales(): bool
    {
        return $this->sales === true;
    }

    public function getUsersProfileEvent(): UserProfileEventUid
    {
        return new UserProfileEventUid($this->users_profile_event);
    }

    public function getUsersProfileStatus(): ?UserProfileStatus
    {
        return $this->users_profile_status ? new UserProfileStatus($this->users_profile_status) : null;
    }

    public function getUsersProfileUsername(): ?string
    {
        return $this->users_profile_username;
    }

    public function getUsersProfileAvatar(): ?string
    {
        return $this->users_profile_avatar;
    }

    public function getUsersProfileAvatarExt(): ?string
    {
        return $this->users_profile_avatar_ext;
    }

    public function getUsersProfileAvatarCdn(): ?bool
    {
        return $this->users_profile_avatar_cdn === true;
    }

    public function getAccountEmail(): ?AccountEmail
    {
        return $this->account_email ? new AccountEmail($this->account_email) : null;
    }

    public function getAccountStatus(): ?EmailStatus
    {
        return $this->account_status ? new EmailStatus($this->account_status) : null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}