# BaksDev Wildberries Bundle — Руководство для разработчиков

## Обзор проекта

**BaksDevWildberriesBundle** — это Symfony-бандл, предоставляющий интеграцию с API Wildberries (маркетплейс товаров в России) для управления товарами, заказами, остатками и продажами.

- **Версия:** 7.4.9
- **Тип:** Symfony Bundle / Composer Package
- **Лицензия:** MIT
- **Требования:** PHP 8.4+, baks-dev/core ^7.4
- **Структура:** DDD + CQRS архитектура

## Быстрый старт

### Установка

```bash
composer require baks-dev/wildberries
php bin/console baks:assets:install
```

### Базовое использование

```php
use BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses\ProfileWarehousesRequest;

$warehouses = new ProfileWarehousesRequest(
    environment: $environment,
    logger: $logger,
    TokenByProfile: $tokenByProfile,
    WbToken: $wbToken,
    cache: $cache,
);

foreach ($warehouses->profile($profileUid)->warehouses() as $warehouse) {
    echo $warehouse->getId() . ': ' . $warehouse->getName();
}
```

## Архитектура

### Структура модуля

```
BaksDev\Wildberries\
├── Api/                    # API-клиенты Wildberries
│   ├── Wildberries.php     # Базовый класс для всех API
│   └── Token/
│       ├── Reference/      # Справочники (категории, характеристики)
│       └── Warehouse/      # Работа со складами
├── Entity/                 # Doctrine сущности
│   ├── WbToken.php         # Основная сущность токена
│   └── Event/              # События токена (audit trail)
├── UseCase/                # Бизнес-логика (CQRS)
│   └── Admin/
│       ├── NewEdit/        # Создание и редактирование
│       └── Delete/         # Удаление
├── Repository/             # Репозитории Doctrine
├── Type/                   # Value Objects и Type Classes
│   ├── Authorization/      # Авторизация
│   ├── Event/              # Типы событий
│   ├── Token/              # Типы токенов
│   └── id/                 # ID-типы
├── Messenger/              # Сообщения Symfony Messenger
├── Listeners/              # Doctrine listeners
├── Security/               # RBAC и безопасность
├── Controller/             # HTTP контроллеры
└── Resources/
    ├── config/             # Конфигурация Symfony
    └── view/               # Twig шаблоны
```

### Паттерны проектирования

| Паттерн | Применение |
|---------|------------|
| **DDD (Domain-Driven Design)** | Разделение на слои: Domain, UseCase, Interface |
| **CQRS** | Команды (DTO + Handler) и запросы (Repository) |
| **Repository** | Абстракция доступа к данным |
| **Value Object** | Неизменяемые типы (WbTokenUid, WbTokenString) |
| **DTO** | Объекты передачи данных для форм и API |
| **Messenger** | Асинхронная обработка событий |
| **Listener** | Doctrine lifecycle callbacks |

## Основные компоненты

### 1. API Клиенты

**Wildberries (базовый класс)** — абстрактный класс для всех API Wildberries

```php
// Установка профиля
$api->profile(UserProfileUid $profile): self

// Установка токена
$api->forTokenIdentifier(WbTokenUid $token): self

// Получение HTTP клиента
$api->TokenHttpClient(): RetryableHttpClient

// Выбор домена API
$api->marketplace(): self      // marketplace-api.wildberries.ru
$api->content(): self          // content-api.wildberries.ru
$api->discountsPrices(): self  // discounts-prices-api.wildberries.ru
$api->buyerChat(): self        // buyer-chat-api.wildberries.ru
$api->feedbacks(): self        // feedbacks-api.wildberries.ru
$api->analytics(): self        // seller-analytics-api.wildberries.ru
$api->statistics(): self       // statistics-api.wildberries.ru

// Получение настроек токена
$api->getPercent(): string     // Торговая наценка
$api->isCard(): bool           // Обновление карточек
$api->isStock(): bool          // Обновление остатков
$api->isOrders(): bool         // Обновление заказов
$api->isSales(): bool          // Обновление продаж
$api->getWarehouse(): ?string  // ID склада
```

### 2. Сущности

**WbToken** — основная сущность токена

```php
#[ORM\Entity]
#[ORM\Table(name: 'wb_token')]
class WbToken
{
    private WbTokenUid $id;      // ID токена
    private WbTokenEventUid $event;  // ID последнего событие
}
```

**WbTokenEvent** — событие изменения токена (audit trail)

```php
#[ORM\Entity]
#[ORM\Table(name: 'wb_token_event')]
class WbTokenEvent extends EntityEvent
{
    private WbTokenUid $main;       // ID токена

    // Связанные данные событие
    private ?WbTokenProfile $profile = null;
    private ?WbTokenValue $token = null;
    private ?WbTokenActive $active = null;
    private ?WbTokenCard $card = null;
    private ?WbTokenStocks $stock = null;
    private ?WbTokenOrders $orders = null;
    private ?WbTokenSales $sales = null;
    private ?WbTokenPercent $percent = null;
    private ?WbTokenWarehouse $warehouse = null;
    private WbTokenModify $modify;  // Метаданные изменения
}
```

### 3. UseCase (Бизнес-логика)

**WbTokenHandler** — обработчик команды создания/обновления токена

```php
public function handle(WbTokenDTO $command): WbToken|string

// Алгоритм работы:
// 1. Валидация DTO
// 2. Пред-сохранение событий (preEventPersistOrUpdate)
// 3. Flush в БД
// 4. Dispatch сообщения в Messenger
```

**WbTokenDTO** — объект передачи данных

```php
class WbTokenDTO implements WbTokenEventInterface
{
    private ?WbTokenEventUid $id = null;
    private WbTokenActiveDTO $active;
    private WbTokenProfileDTO $profile;
    private WbTokenValueDTO $token;
    private WbTokenPercentDTO $percent;
    private WbTokenCardDTO $card;
    private WbTokenStockDTO $stock;
    private WbTokenOrdersDTO $orders;
    private WbTokenWarehouseDTO $warehouse;
    private WbTokenSalesDTO $sales;
}
```

**DTO Компоненты:**
- `WbTokenActiveDTO` — статус (активен/заблокирован)
- `WbTokenProfileDTO` — профиль пользователя
- `WbTokenValueDTO` — токен доступа
- `WbTokenPercentDTO` — наценка
- `WbTokenCardDTO` — флаг обновления карточек
- `WbTokenStockDTO` — флаг обновления остатков
- `WbTokenOrdersDTO` — флаг обновления заказов
- `WbTokenWarehouseDTO` — ID склада
- `WbTokenSalesDTO` — флаг продаж

### 4. Репозитории

**WbTokenRepository** — основной репозиторий токена

```php
interface WbTokenInterface
{
    public function forTokenIdentifier(WbTokenUid $identifier): self;
    public function find(): ?WbToken;
}
```

**WbTokenByProfileRepository** — репозиторий по профилю

```php
interface WbTokenByProfileInterface
{
    public function forProfile(UserProfileUid $profile): self;
    public function getToken(): ?WbAuthorizationToken;
}
```

**AllWbTokenRepository** — репозиторий для списка токенов

```php
interface AllWbTokenInterface
{
    public function profile(UserProfileUid $profile): self;
    public function search(SearchDTO $search): self;
    public function findPaginator(): PaginatorResult;
}
```

### 5. Справочники

**ProfileWarehousesRequest** — получение списка складов

```php
// API: GET /api/v3/warehouses
public function warehouses(): Generator|false
```

**WbObject** — получение категорий товаров

```php
// API: GET /content/v2/object/all
public function findObject(): Generator|false
```

**WbCharacteristicByObjectName** — получение характеристик категории

```php
// API: GET /content/v1/object/characteristics/{name}
public function findCharacteristics(): Generator
```

**ConfigCard** — получение конфигурации карточки

```php
// API: GET /api/v1/config/get/object/translated
public function get(string $name): ConfigCardDTO|false
```

## Работа с Базой Данных

### Таблица: `wb_token`

```sql
CREATE TABLE wb_token (
    id CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokenuid)',
    event CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokeneventuid)',
    PRIMARY KEY(id)
);
```

### Таблица: `wb_token_event`

```sql
CREATE TABLE wb_token_event (
    id CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokeneventuid)',
    main CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokenuid)',
    profile CHAR(36) DEFAULT NULL COMMENT '(DC2Type:userprofileuid)',
    token VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:wbtokenstring)',
    active TINYINT(1) DEFAULT NULL,
    percent VARCHAR(255) DEFAULT NULL,
    card TINYINT(1) DEFAULT NULL,
    stock TINYINT(1) DEFAULT NULL,
    orders TINYINT(1) DEFAULT NULL,
    sales TINYINT(1) DEFAULT NULL,
    warehouse VARCHAR(255) DEFAULT NULL,
    modify_id CHAR(36) DEFAULT NULL,
    PRIMARY KEY(id)
);
```

## Асинхронная обработка

### Messenger

**WbTokenMessage** — сообщение для обновления токена

```php
class WbTokenMessage
{
    public function __construct(
        WbTokenUid $main,
        WbTokenEventUid $event,
        WbTokenEventUid $dtoEvent
    )
}
```

**Транспорт:** `wildberries`

**WildberriesSender** — отправитель сообщений в очередь Wildberries

### Кеширование

Кеширование справочников на уровне Symfony Cache:
- FilesystemAdapter для кеширования
- TTL: 24 часа (категории, характеристики, конфигурация карточек)

## Безопасность

### Роли доступа

- `ROLE_WB_TOKEN` — доступ к управлению токенами Wildberries

### Входные точки

- `/admin/wb/tokens/{page}` — список токенов (`wildberries:admin.index`)
- `/admin/wb/tokens/new` — создание токена (`wildberries:admin.newedit.new`)
- `/admin/wb/tokens/{id}/edit` — редактирование токена (`wildberries:admin.newedit.edit`)
- `/admin/wb/tokens/{id}/delete` — удаление токена (`wildberries:admin.delete`)

### Voters

- `VoterIndex` — просмотр списка токенов
- `VoterNew` — создание токена
- `VoterEdit` — редактирование токена
- `VoterDelete` — удаление токена

## Конфигурация

### services.php

```php
$services->load($NAMESPACE, $PATH)
    ->exclude([
        $PATH.'{Entity,Resources,Type}',
        $PATH.'**'.DIRECTORY_SEPARATOR.'*Message.php',
        $PATH.'**'.DIRECTORY_SEPARATOR.'*Result.php',
        $PATH.'**'.DIRECTORY_SEPARATOR.'*DTO.php',
        $PATH.'**'.DIRECTORY_SEPARATOR.'*Test.php',
    ]);
```

### routes.php

```php
$routes->import($MODULE.'Controller', 'attribute', false)
    ->prefix(Locale::routes())
    ->namePrefix('wildberries:');
```

## Примеры использования

### Получение списка складов

```php
use BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses\ProfileWarehousesRequest;

$warehouses = new ProfileWarehousesRequest(
    environment: $environment,
    logger: $logger,
    TokenByProfile: $tokenByProfile,
    WbToken: $wbToken,
    cache: $cache,
);

$warehouses->profile($profileUid);

foreach ($warehouses->warehouses() as $warehouse) {
    echo $warehouse->getId() . ': ' . $warehouse->getName() . PHP_EOL;
}
```

### Создание токена

```php
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenHandler;

$command = new WbTokenDTO();
$command->getActive()->setValue(true);
$command->getProfile()->setValue($profileUid);
$command->getToken()->setValue('your-token-here');
$command->getPercent()->setValue('10');
$command->getCard()->setValue(true);
$command->getStock()->setValue(true);
$command->getOrders()->setValue(true);
$command->getSales()->setValue(true);
$command->getWarehouse()->setValue('warehouse-id');

$handler = new WbTokenHandler(...);
$result = $handler->handle($command);
```

### Получение категорий товаров

```php
use BaksDev\Wildberries\Api\Token\Reference\Object\WbObject;

$category = new WbObject(...);
$category->profile($profileUid);

foreach ($category->findObject() as $object) {
    echo $object->getName();
}
```

## Тестирование

```bash
php bin/phpunit --group=wildberries
```

## Требования

- PHP >= 8.4
- Symfony 7.1+
- Doctrine ORM
- baks-dev/core ^7.4

## Связанные документы

- **PRODUCT.md** — Продуктовая спецификация с пользовательскими сценариями
- **TECH.md** — Техническая спецификация с архитектурой
- **README.md** — Краткое описание и установка

## Wildberries OpenAPI

[Документация API Wildberries](https://dev.wildberries.ru/docs/openapi/)

## Лицензия

MIT License — см. [LICENSE.md](LICENSE.md)
