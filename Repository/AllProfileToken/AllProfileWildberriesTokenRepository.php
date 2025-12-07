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

namespace BaksDev\Wildberries\Repository\AllProfileToken;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Wildberries\Entity\Event\Active\WbTokenActive;
use BaksDev\Wildberries\Entity\Event\Profile\WbTokenProfile;
use BaksDev\Wildberries\Entity\WbToken;
use Generator;

final class AllProfileWildberriesTokenRepository implements AllProfileWildberriesTokenInterface
{

    private bool $active = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function onlyActiveToken(): self
    {
        $this->active = true;
        return $this;
    }

    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(WbToken::class, 'token');

        if($this->active)
        {
            $dbal->join(
                'token',
                WbTokenActive::class,
                'wb_token_active',
                'wb_token_active.event = token.event AND wb_token_active.value IS TRUE',
            );
        }


        $dbal->join(
            'token',
            WbTokenProfile::class,
            'wb_token_profile',
            'wb_token_profile.event = token.event',
        );

        $dbal
            ->join(
                'wb_token_profile',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = wb_token_profile.value AND users_profile_info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        $dbal->join(
            'users_profile_info',
            UserProfile::class,
            'users_profile',
            'users_profile.id = users_profile_info.profile',
        );


        $dbal->leftJoin(
            'users_profile',
            UserProfilePersonal::class,
            'personal',
            'personal.event = users_profile.event',
        );

        /** Параметры конструктора объекта гидрации */
        $dbal->addSelect('wb_token_profile.value');
        $dbal->addSelect('personal.username AS attr');

        return $dbal
            ->enableCache('wildberries', '1 minutes')
            ->fetchAllHydrate(UserProfileUid::class);
    }


}
