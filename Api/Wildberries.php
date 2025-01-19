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

namespace BaksDev\Wildberries\Api;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

abstract class Wildberries
{

    private ?WbAuthorizationToken $wbAuthorizationToken = null;

    private WbTokenByProfileInterface $TokenByProfile;

    protected LoggerInterface $logger;

    protected ?UserProfileUid $profile = null;

    private array $headers;

    public function __construct(
        WbTokenByProfileInterface $TokenByProfile,
        LoggerInterface $WildberriesLogger,
    )
    {
        $this->TokenByProfile = $TokenByProfile;
        $this->logger = $WildberriesLogger;
    }


    public function profile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Profile
     */
    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }


    public function TokenHttpClient(?WbAuthorizationToken $WbAuthorizationToken = null): RetryableHttpClient
    {
        if($WbAuthorizationToken !== null)
        {
            $this->wbAuthorizationToken = $WbAuthorizationToken;
            $this->profile = $WbAuthorizationToken->getProfile();
        }

        if($this->wbAuthorizationToken === null)
        {
            if(!$this->profile)
            {
                $this->logger->critical('Не указан идентификатор профиля пользователя через вызов метода profile', [self::class.':'.__LINE__]);

                throw new InvalidArgumentException(
                    'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
                );
            }

            $this->wbAuthorizationToken = $this->TokenByProfile->getToken($this->profile);

            if(!$this->wbAuthorizationToken)
            {
                throw new DomainException(sprintf('Токен авторизации Wildberries не найден: %s', $this->profile));
            }
        }

        $this->headers = ['Authorization' => $this->wbAuthorizationToken->getToken()];

        return new RetryableHttpClient(
            HttpClient::create(['headers' => $this->headers])
                ->withOptions([
                    'base_uri' => 'https://suppliers-api.wildberries.ru',
                    'verify_host' => false
                ])
        );
    }


    protected function CookieHttpClient(): RetryableHttpClient
    {
        if(!$this->profile)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }

        $WbAuthorizationCookie = $this->TokenByProfile->getTokenCookie($this->profile);

        if(!$WbAuthorizationCookie)
        {
            throw new DomainException(sprintf('Cookie авторизации Wildberries не найдены: %s', $this->profile));
        }

        $this->headers = [
            'Content-Type' => 'application/json',
            'Cookie' => 'WBToken='.$WbAuthorizationCookie->getToken().'; x-supplier-id='.
                $WbAuthorizationCookie->getIdentifier().';',
        ];

        return new RetryableHttpClient(
            HttpClient::create(['headers' => $this->headers])
                ->withOptions([
                    'base_uri' => 'https://seller.wildberries.ru/',
                ])
        );
    }

    protected function getCurlHeader(): string
    {
        $this->headers['accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';

        return '-H "'.implode('" -H "', array_map(
                function($key, $value) {
                    return "$key: $value";
                },
                array_keys($this->headers),
                $this->headers
            )).'"';
    }

}