# Техническая спецификация: Wildberries API Bundle

## Архитектура

### Общая структура

```
BaksDev\Wildberries\
├── Api/                    # API-клиенты Wildberries
│   ├── Wildberries.php     # Базовый класс для всех API
│   └── Token/
│       ├── Reference/      # Справочники (категории, характеристики)
│       └── Warehouse/      # Работа со складами
├── Entity/                 # Сущности Doctrine
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


| Паттерн                 | Применение                                                |
| ------------------------------ | ------------------------------------------------------------------- |
| **DDD (Domain-Driven Design)** | Разделение на слои: Domain, UseCase, Interface      |
| **CQRS**                       | Команды (DTO + Handler) и запросы (Repository)       |
| **Repository**                 | Абстракция доступа к данным                 |
| **Value Object**               | Неизменяемые типы (WbTokenUid, WbTokenString)       |
| **DTO**                        | Объекты передачи данных для форм и API |
| **Messenger**                  | Асинхронная обработка событий            |
| **Listener**                   | Doctrine lifecycle callbacks                                        |

---

## Классы API

### Wildberries (базовый класс)

**Расположение:** `Api/Wildberries.php`

**Основные методы:**

```php
// Установка профиля пользователя
public function profile(UserProfile|UserProfileUid $profile): self

// Установка токена по идентификатору
public function forTokenIdentifier(WbToken|WbTokenUid $identifier): self

// Получение HTTP клиента с заголовками
public function TokenHttpClient(): RetryableHttpClient

// Методы для выбора API
public function marketplace(): self      // marketplace-api.wildberries.ru
public function content(): self          // content-api.wildberries.ru
public function discountsPrices(): self  // discounts-prices-api.wildberries.ru
public function buyerChat(): self        // buyer-chat-api.wildberries.ru
public function feedbacks(): self        // feedbacks-api.wildberries.ru
public function analytics(): self        // seller-analytics-api.wildberries.ru
public function statistics(): self       // statistics-api.wildberries.ru

// Получение настроек токена
public function getPercent(): string     // Торговая наценка
public function isCard(): bool           // Обновление карточек
public function isStock(): bool          // Обновление остатков
public function isOrders(): bool         // Обновление заказов
public function isSales(): bool          // Обновление продаж
public function getWarehouse(): ?string  // ID склада
```

### ProfileWarehousesRequest

**Расположение:** `Api/Token/Warehouse/ProfileWarehouses/ProfileWarehousesRequest.php`

**Методы:**

```php
// Получить список складов продавца
public function warehouses(): Generator|false
```

**API:** `GET /api/v3/warehouses`

### WbObject

**Расположение:** `Api/Token/Reference/Object/WbObject.php`

**Методы:**

```php
// Получить категории товаров
public function findObject(): Generator|false
```

**API:** `GET /content/v2/object/all`

### WbCharacteristicByObjectName

**Расположение:** `Api/Token/Reference/Characteristics/WbCharacteristicByObjectName.php`

**Методы:**

```php
// Установка названия категории
public function name(string $name): self

// Получить характеристики категории
public function findCharacteristics(): Generator
```

**API:** `GET /content/v1/object/characteristics/{name}`

### ConfigCard

**Расположение:** `Api/Token/Reference/ConfigCard/ConfigCard.php`

**Методы:**

```php
// Получить конфигурацию карточки
public function get(string $name): ConfigCardDTO|false
```

**API:** `GET /api/v1/config/get/object/translated`

---

## Сущности и типы

### WbToken (Основная сущность)

**Расположение:** `Entity/WbToken.php`

```php
#[ORM\Entity]
#[ORM\Table(name: 'wb_token')]
class WbToken
{
    private WbTokenUid $id;      // ID токена
    private WbTokenEventUid $event;  // ID последнего события
}
```

### WbTokenEvent (Событие токена)

**Расположение:** `Entity/Event/WbTokenEvent.php`

```php
#[ORM\Entity]
#[ORM\Table(name: 'wb_token_event')]
class WbTokenEvent extends EntityEvent
{
    private WbTokenEventUid $id;    // ID события
    private WbTokenUid $main;       // ID токена
  
    // Связанные данные события
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

### Value Objects

**ID Типы:**

- `WbTokenUid` — UUID токена
- `WbTokenEventUid` — UUID события

**Типы данных:**

- `WbTokenString` — строка токена доступа
- `WbTokenEventType` — тип события

**Авторизация:**

- `WbAuthorizationToken` — объект авторизации с настройками

---

## UseCase (Бизнес-логика)

### WbTokenHandler (Обработчик команды)

**Расположение:** `UseCase/Admin/NewEdit/WbTokenHandler.php`

```php
public function handle(WbTokenDTO $command): WbToken|string
```

**Алгоритм:**

1. Валидация DTO
2. Пред-сохранение событий (preEventPersistOrUpdate)
3. Flush в БД
4. Dispatch сообщения в Messenger

### WbTokenDTO (Объект передачи данных)

**Расположение:** `UseCase/Admin/NewEdit/WbTokenDTO.php`

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

---

## Репозитории

### WbTokenRepository

**Расположение:** `Repository/WbToken/WbTokenRepository.php`

**Интерфейс:**

```php
interface WbTokenInterface
{
    public function forTokenIdentifier(WbTokenUid $identifier): self;
    public function find(): ?WbToken;
}
```

### WbTokenByProfileRepository

**Расположение:** `Repository/WbTokenByProfile/WbTokenByProfileRepository.php`

**Интерфейс:**

```php
interface WbTokenByProfileInterface
{
    public function forProfile(UserProfileUid $profile): self;
    public function getToken(): ?WbAuthorizationToken;
}
```

### AllWbTokenRepository

**Расположение:** `Repository/AllWbToken/AllWbTokenRepository.php`

**Интерфейс:**

```php
interface AllWbTokenInterface
{
    public function profile(UserProfileUid $profile): self;
    public function search(SearchDTO $search): self;
    public function findPaginator(): PaginatorResult;
}
```

---

## Messenger

### WbTokenMessage

**Расположение:** `Messenger/WbTokenMessage.php`

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

**Transport:** `wildberries`

### WildberriesSender

**Расположение:** `Messenger/Api/WildberriesSender.php`

Отправка сообщений в очередь Wildberries.

---

## Listeners

### WbTokenModifyListener

**Расположение:** `Listeners/Entity/WbTokenModifyListener.php`

**Событие:** `prePersist` для `WbTokenModify`

**Функции:**

- Установка пользователя (USR)
- Установка IP-адреса
- Установка User-Agent

---

## Security

### Role

**Расположение:** `Security/Role.php`

```php
class Role implements RoleInterface, MenuAdminInterface
{
    public const string ROLE = 'ROLE_WB_TOKEN';
    public const string KEY = 'BxDCasSuc';
}
```

### Voters

- `VoterIndex` — просмотр списка токенов
- `VoterNew` — создание токена
- `VoterEdit` — редактирование токена
- `VoterDelete` — удаление токена

---

## Конфигурация

### services.php

**Расположение:** `Resources/config/services.php`

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

**Расположение:** `Resources/config/routes.php`

```php
$routes->import($MODULE.'Controller', 'attribute', false)
    ->prefix(Locale::routes())
    ->namePrefix('wildberries:');
```

**Маршруты:**

- `wildberries:admin.index` — `/admin/wb/tokens/{page}`
- `wildberries:admin.new` — `/admin/wb/tokens/new`
- `wildberries:admin.edit` — `/admin/wb/tokens/{id}/edit`
- `wildberries:admin.delete` — `/admin/wb/tokens/{id}/delete`

---

## База данных

### Таблица: wb_token

```sql
CREATE TABLE wb_token (
    id CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokenuid)',
    event CHAR(36) NOT NULL COMMENT '(DC2Type:wbtokeneventuid)',
    PRIMARY KEY(id)
);
```

### Таблица: wb_token_event

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

---

## Установка

### Composer

```bash
composer require baks-dev/wildberries
```

### Конфигурация

```bash
php bin/console baks:assets:install
```

### Тестирование

```bash
php bin/phpunit --group=wildberries
```

---

## Требования

- PHP >= 8.4
- Symfony 7.4+
- Doctrine ORM
- baks-dev/core ^7.4

---

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
