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

namespace BaksDev\Wildberries\Repository\AllWbToken;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see WbTokenPaginatorResult */
final readonly class WbTokenPaginatorResult
{
    public function __construct(
        private string $id, // " => "019aeb45-6f0c-7a52-afce-de57ac697431"
        private string $event, // " => "019aebbf-3308-7e02-9aba-dd08de19fa43"
        private bool $active, // " => false
        private bool $card, // " => true
        private bool $stocks, // " => false
        private string $users_profile_event, // " => "019a7449-16d9-74c4-b24c-f22366a0780b"
        private ?string $users_profile_status, // " => null
        private ?string $users_profile_username, // " => "White Sign"
        private ?string $users_profile_avatar, // " => null
        private ?string $users_profile_avatar_ext, // " => null
        private ?bool $users_profile_avatar_cdn, // " => null
        private ?string $account_email, // " => null
        private ?string $account_status, // " => null

    ) {}

    public function getId(): WbTokenUid
    {
        return new WbTokenUid($this->id);
    }

    public function getEvent(): WbTokenEventUid
    {
        return new WbTokenEventUid($this->event);
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

}