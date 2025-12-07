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

namespace BaksDev\Wildberries\Repository\AllWbTokensByProfile;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\Active\WbTokenActive;
use BaksDev\Wildberries\Entity\Event\Card\WbTokenCard;
use BaksDev\Wildberries\Entity\Event\Profile\WbTokenProfile;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use Generator;
use InvalidArgumentException;


final class AllWbTokensByProfileRepository implements AllWbTokensByProfileInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function forProfile(UserProfile|UserProfileUid $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод возвращает идентификаторы токенов профиля пользователя
     *
     * @return Generator<WbTokenUid>|false
     */
    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException('Invalid Argument UserProfileUid');
        }

        $dbal->select('wb_token.id AS value');
        $dbal->from(WbToken::class, 'wb_token');

        $dbal->join(
            'wb_token',
            WbTokenProfile::class,
            'wb_token_profile',
            'wb_token_profile.event = wb_token.event AND wb_token_profile.value = :profile',
        )
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE,
            );

        $dbal->join(
            'wb_token',
            WbTokenActive::class,
            'wb_token_active',
            'wb_token_active.event = wb_token.event AND wb_token_active.value IS TRUE',
        );


        $dbal->leftJoin(
            'wb_token',
            WbTokenCard::class,
            'wb_token_card',
            'wb_token_card.event = wb_token.event',
        );

        $dbal->addOrderBy('wb_token_card.value', 'DESC');

        return $dbal
            ->enableCache('wildberries')
            ->fetchAllHydrate(WbTokenUid::class);

    }
}