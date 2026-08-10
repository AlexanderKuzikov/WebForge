# WebForge — DECISIONS

<!-- Append-only. Формат фиксирован. -->

## 2026-05-03: webforge.json как SSOT

**Контекст:** Нужен единый источник истины для генерации сайта.

**Решение:** Один JSON-файл описывает всю структуру, контент, тему, медиа, schema.org.

**Альтернативы:** Markdown + frontmatter, YAML конфиги, CMS.

**Trade-off:** Большой файл, но единая точка управления для AI и человека.

## 2026-05-03: PHP для разработки, статика для деплоя

**Контекст:** Хостинг без Node.js, нужна автономность.

**Решение:** PHP include-based разработка, build.php генерирует чистый HTML/CSS/JS.

**Альтернативы:** Next.js SSG, Hugo, Eleventy.

**Trade-off:** Нет hot-reload, но zero-dependency на продакшене.
