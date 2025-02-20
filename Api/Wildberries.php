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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\Cache\CacheInterface;

abstract class Wildberries
{
    private ?WbAuthorizationToken $wbAuthorizationToken = null;

    protected UserProfileUid|false $profile = false;

    private array $headers;

    private string|false $base = false;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $environment,
        #[Target('wildberriesLogger')] protected LoggerInterface $logger,
        private readonly WbTokenByProfileInterface $TokenByProfile,
        private readonly AppCacheInterface $cache,
    ) {}


    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid|false
    {
        return $this->profile;
    }

    protected function content(): self
    {
        $this->base = 'content-api.wildberries.ru';

        return $this;
    }

    protected function marketplace(): self
    {
        $this->base = 'marketplace-api.wildberries.ru';

        return $this;
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
            if(false === $this->profile)
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

        $this->base ?: $this->base = 'suppliers-api.wildberries.ru';

        $this->headers['Authorization'] = $this->wbAuthorizationToken->getToken();
        $this->headers['accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';

        return new RetryableHttpClient(
            HttpClient::create(['headers' => $this->headers])
                ->withOptions([
                    'base_uri' => sprintf('https://%s', $this->base),
                    'verify_host' => false
                ])
        );
    }

    protected function CookieHttpClient(): RetryableHttpClient
    {
        if(false === $this->profile)
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


    /**
     * Метод проверяет что окружение является PROD,
     * тем самым позволяет выполнять операции запроса на сторонний сервис
     * ТОЛЬКО в PROD окружении
     */
    protected function isExecuteEnvironment(): bool
    {
        return $this->environment === 'prod';
    }

    protected function getCacheInit(string $namespace): CacheInterface
    {
        return $this->cache->init($namespace);
    }

}