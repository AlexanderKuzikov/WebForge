<p align="center">
  <a href="https://www.php.net/"><img alt="PHP 8.3" src="https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white"></a>
  <a href="https://nodejs.org/"><img alt="Node" src="https://img.shields.io/badge/Node-tools-339933?logo=node.js&logoColor=white"></a>
  <a href="LICENSE"><img alt="License" src="https://img.shields.io/badge/License-Apache_2.0-blue.svg"></a>
</p>

<h1 align="center">WebForge</h1>
<p align="center">PHP Static Site Generator с нулевыми зависимостями на продакшене</p>

---

Универсальный генератор статических сайтов. PHP для разработки, чистый HTML/CSS/JS для деплоя. Любой хостинг без Node.js, Python, баз данных. AI-friendly архитектура: webforge.json как единый источник истины.

- **webforge.json** — SSOT: структура, контент, тема, медиа, schema.org
- **PHP для разработки** — include-based, удобная отладка
- **Статика для деплоя** — build.php генерирует чистый HTML/CSS/JS
- **Mobile-First** — от 320px вверх, rem/em/%/vw
- **SEO-first** — Critical CSS, Schema.org, OG-теги, семантика
- **Без фреймворков** — HTML/CSS/JS с нуля
- **AI-friendly** — архитектура оптимизирована для AI-ассистентов

## Быстрый старт

```bash
git clone https://github.com/AlexanderKuzikov/WebForge.git
```

Проект в фазе архитектуры. Production-кейс: [Zavodsvay-Static](https://github.com/AlexanderKuzikov/Zavodsvay-Static).

## Документация

- [`docs/CONTEXT.md`](docs/CONTEXT.md) — состояние проекта
- [`docs/DECISIONS.md`](docs/DECISIONS.md) — архитектурные решения

## Статус

**Архитектура определена.** Генераторы (webforge_php_generator.php, build.php) ещё не написаны. 5 open-вопросов.

## Лицензия

[Apache-2.0](LICENSE) © Alexander Kuzikov
