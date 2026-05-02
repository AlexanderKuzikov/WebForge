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

---

## Связь с Zavodsvay-Static

**[Zavodsvay-Static](https://github.com/AlexanderKuzikov/Zavodsvay-Static)** — первый production-кейс WebForge.  
Сейчас это **pre-static версия**: работает на минимальном PHP (file-router, layouts, partials) без генератора.  
Когда WebForge будет готов — Zavodsvay-Static мигрирует на полноценный pipeline WebForge.

**Общие задачи двух проектов:**
- Медиапайплайн (Sharp + VLM alt) — разрабатывается как часть WebForge, применяется в Zavodsvay
- Schema.org / OG генерация — архитектура в WebForge, применение в Zavodsvay
- Карта 500 объектов (MapLibre + PMTiles) — уникальный блок Zavodsvay, войдёт в компоненты WebForge
- CSS-система компонентов — решается в WebForge, переносится в Zavodsvay при миграции
- sitemap.xml — в Zavodsvay сейчас вручную, в WebForge будет генерироваться при build

---

## Текущее состояние проекта

**Дата последнего обновления:** 2026-05-02

### Что определено и зафиксировано
- Архитектура SSOT через `webforge.json` (страницы, тема, медиа, schema, объекты)
- Флоу: `webforge.json → webforge_php_generator.php → _dev_site/ → build.php → build/`
- Компонентная система: изоляция HTML ID, JS изоляция через контекст элемента
- CSS: модульный per-component `style.css`, scoped через `.c-{name}` при сборке
- PageBuilder: структурные шаблоны в `webforge_page_structure_templates.json`
- Медиапайплайн: Node + Sharp, AVIF+WebP, alt через VLM, реестр в `webforge.json[media]`
- Карта объектов: MapLibre GL + PMTiles (автономность, WebGL)
- SEO: Schema.org из данных при build, OG/Twitter шаблонизированы, geo-теги

### Что не реализовано (очередь)
- [ ] CSS-нейминг: решение scoped prefix при сборке (открытый вопрос с День 0)
- [ ] `webforge_php_generator.php` — не написан
- [ ] `build.php` — не написан (обновлённая роль)
- [ ] `tools/process-media.js` — не написан
- [ ] `tools/generate-alts.js` — не написан
- [ ] Базовые компоненты (header, footer, hero, nav)
- [ ] Механизм генерации CSS-переменных из `webforge.json`
- [ ] PageBuilder UI

---

## Архитектурные решения

### `webforge.json` как SSOT

Единый JSON-файл содержит всё: структуру страниц, данные компонентов, тему, медиареестр, данные объектов, schema.org-конфигурацию. Для 500-2500 страниц размер ~2-10 МБ — управляем для PHP. Разбивать на файлы нецелесообразно до появления реальных проблем с производительностью.

### Компоненты

```php
// Каждый компонент получает:
$data         // данные экземпляра из webforge.json
$globalConfig // тема, навигация, глобальные настройки

// Уникальность ID:
$componentBaseId = basename(__DIR__); // e.g. "hero"
// → id="hero-title", id="hero-btn"
```

### CSS Scoped Isolation (решение)

При сборке `build.php` парсит `components/{name}/style.css` и оборачивает все правила в `.c-{name}`:
```css
/* Исходник в components/hero/style.css */
.title { font-size: 2rem }

/* После build.php */
.c-hero .title { font-size: 2rem }
```
Разработчик пишет простые классы. Изоляция — автоматическая на этапе сборки.

### JS Изоляция

```js
// script.js компонента
function initHero(element) {
  const btn = element.querySelector('.btn') // только внутри своего элемента
  // ...
}
// PHP генерирует inline:
// document.addEventListener('DOMContentLoaded', () => {
//   initHero(document.getElementById('hero'))
// })
```
Полный отказ от глобальных ID и глобальных событий.

### Медиапайплайн

```
source/{context}/{name}.jpg    ← оригинал (вне деплоя)
  ↓ node tools/process-media.js
assets/img/{context}/{name}-{width}.{avif|webp}  ← нарезанные наборы
  ↓ node tools/generate-alts.js (Qwen VLM)
webforge.json[media][key].alt  ← заполняется, vlm_reviewed: false → ручная проверка
```

Форматы: AVIF (приоритет, ~50% меньше WebP) + WebP (fallback) + JPG (legacy).  
Размеры: 320 / 640 / 1024 / 1600px.  
OG-картинки: 1200×630, отдельная генерация.

### Карта объектов

**MapLibre GL JS + PMTiles** — выбор обоснован:
- Один `.pmtiles` файл с картой региона (~10-50 МБ) — полностью локально
- Нет зависимости от внешних тайл-серверов (автономность)
- WebGL-рендеринг, открытый стандарт, активная поддержка
- Альтернативы отклонены: Leaflet (нет автономности без локальных тайлов), Google Maps (внешняя зависимость), SVG (не масштабируется на 500 точек)

### Schema.org стратегия

`build.php` генерирует JSON-LD из `webforge.json` для каждой страницы:

| Тип страницы | Schema типы |
|---|---|
| Главная | `Organization` + `LocalBusiness` + `WebSite` |
| Статья | + `Article` |
| Объект | + `LocalBusiness` (дочерний) + `ImageObject[]` + `PropertyValue[]` |
| Каталог | + `ItemList` + `Product[]` |

Все схемы объединяются в один `@graph` блок.

### OG / Social разметка

```html
<!-- Все страницы -->
<meta property="og:image" content="{page-specific или дефолтный}">  <!-- 1200×630 -->
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">

<!-- Дополнительно для Яндекса -->
<meta name="geo.region" content="RU-PER">
<meta name="geo.position" content="58.0105;56.2502">
<meta name="ICBM" content="58.0105, 56.2502">
```

### Favicon-набор (минимальный полный)

| Файл | Назначение |
|---|---|
| `favicon.svg` | Современные браузеры, поддержка тёмной темы |
| `favicon.ico` | Legacy fallback (16+32+48px внутри) |
| `apple-touch-icon.png` | 180×180, iOS |
| `icon-192.png` | Android Chrome |
| `icon-512.png` | PWA, maskable |
| `site.webmanifest` | Android/PWA манифест |

---

## Договорённости и решения

| Дата | Решение |
|---|---|
| 2025-08-27 | SSOT: `webforge.json` — единый файл, не дробить |
| 2025-08-27 | JS изоляция: только через контекст элемента, никаких глобальных событий |
| 2025-08-27 | CSS: модульный per-component, scoped через build.php |
| 2025-08-27 | Mobile-first от 320px, `rem`/`em`, Modern Normalize |
| 2025-08-30 | Флоу разработки: json → `_dev_site/` (PHP) → `build/` (static) |
| 2025-08-30 | PageBuilder: структурные шаблоны в отдельном JSON |
| 2026-05-02 | Медиапайплайн: Node + Sharp, AVIF+WebP, VLM alt-генерация |
| 2026-05-02 | Карта: MapLibre GL + PMTiles (автономность) |
| 2026-05-02 | Schema.org: генерируется программно из webforge.json при build |
| 2026-05-02 | Favicon: 6 файлов (SVG+ICO+180+192+512+manifest), без msapplication |
| 2026-05-02 | CSS scoped: auto-wrap `.c-{name}` при сборке, разработчик пишет простые классы |
