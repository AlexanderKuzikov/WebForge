# WebForge — PHP Static Site Generator

[![License](https://img.shields.io/badge/license-Apache%202.0-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Node](https://img.shields.io/badge/Node.js-tools-339933?logo=node.js&logoColor=white)](https://nodejs.org/)

**Контекст разработки (для AI и деталей):** [CONTEXT.md](CONTEXT.md)

WebForge — универсальный генератор статических сайтов с PHP-разработкой и нулевыми зависимостями на продакшене. Позволяет использовать любые инструменты локально (Node, Python, Go, VLM/LLM, Google Sheets, БД) — и генерировать чистый статический HTML/CSS/JS для деплоя на любой хостинг.

**Первый production-кейс:** [Zavodsvay-Static](https://github.com/AlexanderKuzikov/Zavodsvay-Static) — сайт завода винтовых свай «Гефест», Пермь.

---

## Философия

- **Абсолютная автономность** — никаких внешних CDN, API, шрифтов. Всё включено в статический пакет
- **Без фреймворков** — HTML, CSS, JS пишется с нуля; идеи из Bootstrap/других — только как референс
- **Mobile-First** — от 320px вверх, относительные единицы (`rem`, `em`, `%`, `vw/vh`)
- **Минимум JavaScript** — приоритет HTML/CSS, `<details>`/`<summary>` вместо JS-аккордеонов
- **PHP для разработки, статика для деплоя** — удобная отладка локально, нулевая серверная логика на хостинге
- **SEO-first** — Critical CSS, Schema.org, OG-теги, семантика, скорость

---

## Архитектура

```
webforge.json              # SSOT — вся структура, контент, тема, медиа, schema
├── components/            # Компоненты: {name}/{name}.php + style.css + script.js
├── layouts/               # Макеты страниц (main, home, wide...)
├── source/                # Оригиналы медиафайлов (вне деплоя)
├── tools/                 # Утилиты сборки
│   ├── process-media.js   # Нарезка изображений (Node + Sharp): WebP + AVIF
│   ├── generate-alts.js   # Генерация alt-текстов через локальную VLM
│   └── generate-css.php   # CSS-переменные из webforge.json
├── _dev_site/             # Генерируемый PHP-сайт для разработки (gitignore)
├── build/                 # Финальная статическая сборка (gitignore)
├── webforge_php_generator.php  # json → _dev_site/
├── build.php              # _dev_site/ → build/ (HTML + минификация)
└── webforge_page_structure_templates.json  # Структурные шаблоны для PageBuilder
```

---

## Процесс работы

```
webforge.json (SSOT)
  ↓
tools/process-media.js      ← нарезка фото (Sharp: WebP + AVIF × 4 размера)
tools/generate-alts.js      ← alt-тексты через VLM (Qwen/Gemma локально)
  ↓
webforge_php_generator.php  ← генерация _dev_site/ из json
  ↓
php -S localhost:8000 -t _dev_site/   ← разработка + отладка
  ↓
build.php                   ← _dev_site/ → build/ (минификация, Critical CSS, Schema, OG)
  ↓
build/                      ← чистый статический HTML/CSS/JS
  ↓
FTP / GitHub Actions → хостинг
```

---

## Компонентная система

Каждый компонент — изолированная директория:
```
components/hero/
├── hero.php        # PHP-шаблон. Получает $data (экземпляр) и $globalConfig (тема)
├── style.css       # Scoped CSS — при сборке оборачивается в .c-hero {...}
└── script.js       # Опционально. Работает только в контексте своего элемента
```

Уникальность HTML ID: `$componentBaseId = basename(__DIR__)` + суффиксы.  
JS изоляция: `DOMContentLoaded → querySelector → initComponent(element)`, никаких глобальных событий.

---

## `webforge.json` — структура SSOT

```json
{
  "site": {
    "name": "...",
    "url": "...",
    "schema": { "type": "LocalBusiness", "geo": {}, "telephone": "..." },
    "og": { "image": "...", "locale": "ru_RU" }
  },
  "theme": {
    "colors": {}, "typography": {}, "breakpoints": {}
  },
  "navigation": [],
  "pages": [
    {
      "slug": "index",
      "layout": "home",
      "meta": { "title": "...", "description": "..." },
      "schema": { "type": "WebPage" },
      "components": []
    }
  ],
  "media": {
    "hero-main": {
      "source": "source/hero.jpg",
      "alt": "...",
      "widths": [320, 640, 1024, 1600],
      "formats": ["avif", "webp"],
      "generated": false,
      "vlm_reviewed": false
    }
  },
  "objects": []
}
```

---

## Медиапайплайн

**Инструмент:** Node.js + [Sharp](https://sharp.pixelplumbing.com/) — индустриальный стандарт нарезки.  
**Форматы:** AVIF (приоритет) + WebP (fallback) + JPG (legacy fallback).  
**Размеры:** 320 / 640 / 1024 / 1600px.  
**Alt-тексты:** локальная VLM (Qwen/Gemma) — генерация + флаг `vlm_reviewed` для ручной проверки.  
**OG-картинки:** 1200×630, генерируются отдельно для каждой страницы при наличии данных.

---

## SEO-стратегия

- **Schema.org JSON-LD** генерируется из `webforge.json` при build: `Organization`, `LocalBusiness`, `WebSite`, `Article`, `ImageObject`
- **Open Graph + Twitter Cards** — шаблонизированы, `og:image` уникален для каждой страницы
- **Geo-теги** — `geo.region`, `geo.position`, `ICBM` для Яндекса
- **Favicon** — SVG + ICO + apple-touch-icon + `site.webmanifest`
- **sitemap.xml** — генерируется при build из всех страниц `webforge.json`
- **Programmatic SEO** — 500+ уникальных страниц объектов из данных + уникальные фото + уникальные alt

---

## Карта объектов

Для блока интерактивной карты (~500 объектов) — **MapLibre GL + PMTiles**:  
один локальный `.pmtiles` файл с картой региона вместо внешних тайлов.  
Полностью автономно, WebGL-рендеринг, открытый стандарт.

---

## Локальная разработка

Требования: PHP 8.x, Node.js 20+, Python 3.x (для VLM-скриптов), Laragon или аналог.

```bash
# 1. Нарезать медиа
node tools/process-media.js

# 2. Сгенерировать PHP-сайт
php webforge_php_generator.php

# 3. Запустить dev-сервер
php -S localhost:8000 -t _dev_site/

# 4. Собрать статику
php build.php
```

---

## Связанные проекты

| Проект | Статус | Описание |
|---|---|---|
| [Zavodsvay-Static](https://github.com/AlexanderKuzikov/Zavodsvay-Static) | 🟡 Pre-static (PHP) | Production-кейс WebForge. Сайт завода «Гефест», Пермь |

---

## Автор

**Alexander Kuzikov** — [github.com/AlexanderKuzikov](https://github.com/AlexanderKuzikov)

## Лицензия

[Apache License 2.0](LICENSE)

---

© 2025 — 2026 WebForge Project
