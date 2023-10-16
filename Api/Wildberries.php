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

namespace BaksDev\Wildberries\Api;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

abstract class Wildberries
{

    private WbTokenByProfileInterface $TokenByProfile;

    protected LoggerInterface $logger;

    protected ?UserProfileUid $profile = null;

    protected bool $test;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] string $environment,
        WbTokenByProfileInterface $TokenByProfile,
        LoggerInterface $logger,
    )
    {
        $this->test = ($environment === 'test' || $environment === 'api');
        $this->TokenByProfile = $TokenByProfile;
        $this->logger = $logger;
    }


    public function profile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }


    protected function TokenHttpClient(): RetryableHttpClient
    {
        if(!$this->profile)
        {
            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }

        $WbAuthorizationToken = $this->TokenByProfile->getToken($this->profile);

        if(!$WbAuthorizationToken)
        {
            throw new DomainException(sprintf('Токен авторизации Wildberries не найден: %s', $this->profile));
        }

        return new RetryableHttpClient(
            HttpClient::create(['headers' => ['Authorization' => $WbAuthorizationToken->getToken()]])
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

        return new RetryableHttpClient(
            HttpClient::create([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cookie'       => 'WBToken='.$WbAuthorizationCookie->getToken().'; x-supplier-id='.
                        $WbAuthorizationCookie->getIdentifier().';',
                ],
            ])
                ->withOptions([
                    'base_uri' => 'https://seller.wildberries.ru/',
                ])
        );
    }

}