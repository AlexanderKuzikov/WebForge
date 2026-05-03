# CONTEXT.md — Контекст разработки WebForge

> Этот файл — живой документ для AI-ассистента и разработчика.  
> Обновляется после каждой сессии/задачи. Дополняет README.md деталями, решениями и договорённостями.

---

## Режим работы с AI

- Основной инструмент: Perplexity (Space: Zavodsvay), GitHub MCP
- AI имеет право коммитить напрямую в `main` после явного подтверждения
- Общение на «ты». AI — главный разработчик/эксперт, пользователь — архитектор
- Код предоставляется только после обсуждения архитектуры
- Перед каждой сессией рекомендуется дать AI прочитать этот файл + DEV_LOG.md
- **AI не должен молчаливо соглашаться с архитектурными решениями** — критика и указание trade-offs обязательны

---

## Связь с production-кейсами

WebForge — инструмент. Реальные сайты на его базе — production-кейсы. Каждый кейс проходит путь:
**pre-static PHP** (file-router, layouts, partials, минимум PHP) → **WebForge pipeline** (после готовности генератора).

| Проект | Статус | Описание |
|---|---|---|
| [Zavodsvay-Static](https://github.com/AlexanderKuzikov/Zavodsvay-Static) | 🟡 Pre-static (PHP) | Сайт завода «Гефест», Пермь. Активная разработка. |
| _Другие кейсы_ | — | Будут добавлены по мере готовности |

**Общие задачи всех кейсов и WebForge:**
- Медиапайплайн (Sharp + VLM alt) — **реализован в Zavodsvay**, будет портирован в WebForge
- Schema.org / OG генерация — архитектура в WebForge, применение в кейсах
- Карта объектов (MapLibre + PMTiles) — уникальный блок Zavodsvay, войдёт в компоненты WebForge
- CSS-система компонентов — решается в WebForge, переносится в кейсы при миграции
- sitemap.xml — в кейсах сейчас вручную, в WebForge генерируется при build

---

## Граница: «Zavodsvay-specific» vs «WebForge-universal»

Важно различать, что является уникальным для Zavodsvay и что войдёт в ядро WebForge.

| Фича | Принадлежность | Обоснование |
|---|---|---|
| Карта объектов (MapLibre + PMTiles) | **WebForge** (универсальный блок) | Паттерн повторится у других клиентов (каталог, недвижимость, услуги) |
| Geo-теги (`geo.region`, ICBM) | **WebForge** (конфиг в `site.schema.geo`) | Нужно всем локальным бизнесам |
| Data-driven pages (500 объектов) | **WebForge** (первоклассная концепция) | Programmatic SEO — ключевой паттерн |
| Конкретные данные завода Гефест | **Zavodsvay-only** | Контент, не архитектура |
| Hero-видео на главной | **WebForge** (компонент `video-hero`) | Переиспользуемый компонент |
| Структура статей с 28 материалами | **WebForge** (шаблон `article`) | Стандартный паттерн |

---

## Текущее состояние проекта

**Дата последнего обновления:** 2026-05-03

### Что определено и зафиксировано
- Архитектура SSOT через `webforge.json` (страницы, тема, медиа, schema, объекты)
- Флоу: `webforge.json → webforge_php_generator.php → _dev_site/ → build.php → build/`
- Компонентная система: изоляция HTML ID, JS изоляция через контекст элемента
- CSS: модульный per-component `style.css`, scoped через `.c-{name}` при сборке
- PageBuilder: структурные шаблоны в `webforge_page_structure_templates.json`
- Медиапайплайн: прототип реализован в Zavodsvay-Static (см. ниже)
- Карта объектов: MapLibre GL + PMTiles (автономность, WebGL)
- SEO: Schema.org из данных при build, OG/Twitter шаблонизированы, geo-теги
- Hashed assets: `build.php` генерирует `style.{hash8}.css` для cache-busting
- CI/CD: GitHub Actions → FTP-деплой (только изменённые файлы через `lftp mirror`)

### Что не реализовано (очередь)
- [ ] CSS-нейминг: решение scoped prefix при сборке (открытый вопрос с День 0)
- [ ] `webforge_php_generator.php` — не написан
- [ ] `build.php` — не написан (обновлённая роль)
- [ ] `tools/generate-alts.js` — не написан (VLM авто-alt)
- [ ] Базовые компоненты (header, footer, hero, nav)
- [ ] Механизм генерации CSS-переменных из `webforge.json`
- [ ] PageBuilder UI
- [ ] JSON Schema / валидация контракта компонент ↔ `webforge.json`
- [ ] GitHub Actions workflow для FTP-деплоя

---

## Открытые архитектурные вопросы

> Это явный список нерешённых вопросов. AI не должен молчаливо предполагать ответы — нужно поднимать их при работе в соответствующих областях.

### 1. Декомпозиция `webforge.json`
**Проблема:** При 500+ страницах + 500+ объектах + медиареестр файл станет 15–30 МБ. Неудобен для редактирования, git diff, merge conflicts при AI-работе.  
**Варианты:**  
- А) Оставить монолитом — простота, единый контекст для AI  
- Б) Разбить на namespace-файлы (`webforge.pages.json`, `webforge.objects.json`, `webforge.media.json`, `webforge.theme.json`) — merge при build  
**Статус:** Открытый. Решать после завершения Zavodsvay, когда будет ясен реальный объём.

### 2. CSS naming convention для компонентов
**Проблема:** Новые секции в Zavodsvay пишутся без scoped-prefix. При миграции в WebForge компонентный CSS потребует рефакторинга.  
**Решение (предлагаемое):** Новые блоки CSS в Zavodsvay писать сразу в стиле `.c-{name}__element`, чтобы миграция была механической.  
**Статус:** Открытый. Требует явного решения архитектора.

### 3. CSS auto-wrap при сборке
**Проблема:** `build.php` должен парсить CSS и оборачивать в `.c-{name}`. Regex-парсинг CSS ненадёжен (`@media`, `@keyframes`, `:root`, псевдоэлементы).  
**Варианты:**  
- А) Regex с явным списком исключений  
- Б) Соглашение: в `style.css` компонента запрещены `@keyframes`, `:root` — выносятся в глобальный CSS  
- В) Использовать PHP CSS-парсер (sabberworm/php-css-parser)  
**Статус:** Открытый.

### 4. Контракт компонент ↔ данные
**Проблема:** Нет валидации структуры `$data`, которую получает компонент. Ошибка обнаруживается только при build.  
**Решение (предлагаемое):** JSON Schema для каждого компонента + валидация в `webforge_php_generator.php` на старте.  
**Статус:** Открытый. Минимум — документировать ожидаемый `$data` в комментарии компонента.

### 5. `source/` в git — порог перехода на Git LFS
**Проблема:** 500 объектов × оригинальные фото = потенциально сотни МБ в истории. Удалить без `git filter-branch` невозможно.  
**Решение:** Установить порог явно. Предложение: при превышении 100 МБ в `source/` — переходить на LFS.  
**Статус:** Открытый. Решение нужно до начала загрузки фото объектов.

---

## Медиапайплайн — состояние (прототип в Zavodsvay)

Полностью работающий пайплайн реализован в [Zavodsvay-Static/tools/](https://github.com/AlexanderKuzikov/Zavodsvay-Static/tree/main/tools). Портирование в WebForge планируется после стабилизации архитектуры.

### Реализовано
- `process-media.js` — CLI и модуль: сканирует `source/`, регистрирует в `data/media.json`, нарезает WebP
- `server.js` — Express API (порт 3010): скан, нарезка, перегенерация, удаление, orphan-очистка
- `ui/index.html` — самодостаточный UI медиабиблиотеки
- `partials/image.php` — PHP `render_image($key)` с кэшем реестра

### Ключевые решения, принятые в Zavodsvay (потенциально войдут в WebForge)
- Ключ = путь от `source/` без расширения, слэши → дефисы (`logo2`, `objects-obj-001-main`)
- `widths` в JSON = реально сгенерированные размеры (не дефолтный массив)
- `orig_width`/`orig_height` хранятся в реестре → нулевой CLS, `aspect-ratio`
- GIF (вкл. анимированные) → анимированный WebP через `sharp({animated:true})`
- `generated: false` → признак перезаписи (через UI или вручную)
- `source/` хранится в git; при превышении 100 МБ → Git LFS

### Не реализовано (в очереди)
- [ ] `tools/generate-alts.js` (VLM Qwen авто-alt, флаг `vlm_reviewed`)
- [ ] AVIF в дополнение к WebP (сейчас только WebP)
- [ ] Перенос реестра в `webforge.json` после миграции на WebForge-генератор

---

## Архитектурные решения

### `webforge.json` как SSOT

Единый JSON-файл содержит всё: структуру страниц, данные компонентов, тему, медиареестр, данные объектов, schema.org-конфигурацию. Для 500-2500 страниц размер ~2-10 МБ — управляем для PHP. При существенном росте — декомпозиция на namespace-файлы (см. Открытые вопросы).

### Компоненты

```php
// Каждый компонент получает:
$data         // данные экземпляра из webforge.json
$globalConfig // тема, навигация, глобальные настройки

// Уникальность ID:
$componentBaseId = basename(__DIR__); // e.g. "hero"
// → id="hero-title", id="hero-btn"

// Обязательный комментарий-контракт в начале компонента:
/**
 * Ожидаемая структура $data:
 * [
 *   'title'    => string,
 *   'subtitle' => string|null,
 *   'cta_text' => string,
 *   'cta_url'  => string,
 * ]
 */
```

### CSS Scoped Isolation

При сборке `build.php` парсит `components/{name}/style.css` и оборачивает все правила в `.c-{name}`:
```css
/* Исходник в components/hero/style.css */
.title { font-size: 2rem }

/* После build.php */
.c-hero .title { font-size: 2rem }
```

> **Ограничение:** `@keyframes`, `:root`, глобальные сбросы — запрещены в `style.css` компонента. Выносятся в глобальный `assets/css/global.css`.

### JS Изоляция

```js
function initHero(element) {
  const btn = element.querySelector('.btn')
}
// PHP генерирует inline:
// document.addEventListener('DOMContentLoaded', () => {
//   initHero(document.getElementById('hero'))
// })
```

### Data-driven Pages (Programmatic SEO)

Первоклассная концепция WebForge. `webforge.json` описывает шаблон страницы + ссылку на data-файл → генератор итерирует и создаёт N статических страниц:

```json
{
  "slug": "objects/{id}",
  "template": "object-page",
  "data_source": "data/objects.json",
  "data_key": "id"
}
```

### Карта объектов

**MapLibre GL JS + PMTiles**: один `.pmtiles` файл региона, WebGL-рендеринг, полная автономность. Альтернативы отклонены: Leaflet (нет автономности), Google Maps (внешняя зависимость, блокировки).

### Schema.org стратегия

`build.php` генерирует JSON-LD из `webforge.json` для каждой страницы:

| Тип страницы | Schema типы |
|---|---|
| Главная | `Organization` + `LocalBusiness` + `WebSite` |
| Статья | + `Article` |
| Объект | + `LocalBusiness` (дочерний) + `ImageObject[]` + `PropertyValue[]` |
| Каталог | + `ItemList` + `Product[]` |

### OG / Social разметка

```html
<meta property="og:image" content="{page-specific или дефолтный}">  <!-- 1200×630 -->
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="geo.region" content="RU-PER">
<meta name="geo.position" content="58.0105;56.2502">
```

### Hashed Assets (Cache-busting)

`build.php` генерирует `style.{hash8}.css` и вставляет актуальный hash в HTML. Критично для корректного кэширования без CDN на shared hosting.

### Favicon-набор (минимальный полный)

| Файл | Назначение |
|---|---|
| `favicon.svg` | Современные браузеры, тёмная тема |
| `favicon.ico` | Legacy fallback |
| `apple-touch-icon.png` | 180×180, iOS |
| `icon-192.png` | Android Chrome |
| `icon-512.png` | PWA, maskable |
| `site.webmanifest` | Android/PWA манифест |

---

## Договорённости и решения

| Дата | Решение |
|---|---|
| 2025-08-27 | SSOT: `webforge.json` — единый файл, не дробить (пересмотреть при >10 МБ) |
| 2025-08-27 | JS изоляция: только через контекст элемента |
| 2025-08-27 | CSS: модульный per-component, scoped через build.php |
| 2025-08-27 | Mobile-first от 320px, `rem`/`em`, Modern Normalize |
| 2025-08-30 | Флоу разработки: json → `_dev_site/` (PHP) → `build/` (static) |
| 2025-08-30 | PageBuilder: структурные шаблоны в отдельном JSON |
| 2026-05-02 | Карта: MapLibre GL + PMTiles (автономность) |
| 2026-05-02 | Schema.org: генерируется программно из webforge.json при build |
| 2026-05-02 | Favicon: 6 файлов (SVG+ICO+180+192+512+manifest) |
| 2026-05-02 | CSS scoped: auto-wrap `.c-{name}` при сборке |
| 2026-05-03 | Медиапайплайн: прототип реализован в Zavodsvay-Static (портирование в WebForge после стабилизации) |
| 2026-05-03 | GIF (вкл. аним) → аним WebP через `sharp({animated:true})` |
| 2026-05-03 | `orig_width`/`orig_height` в реестре → нулевой CLS |
| 2026-05-03 | Orphan-файлы: очистка через UI |
| 2026-05-03 | Data-driven pages — первоклассная концепция WebForge (programmatic SEO) |
| 2026-05-03 | Hashed assets (`style.{hash8}.css`) при build — cache-busting без CDN |
| 2026-05-03 | `source/` в git; порог перехода на Git LFS — 100 МБ |
| 2026-05-03 | Компонент обязан документировать контракт `$data` в PHPDoc-комментарии |
