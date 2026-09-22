<?php
/**
 * Компонент product-card — карточка товара каталога.
 *
 * Идея: вертикальный ProductCard из Storefront UI (MIT, vuestorefront/storefront-ui):
 * картинка + рейтинг + цена + CTA как copy-paste Block. Код написан с нуля
 * под WebForge: семантика вместо SfLink/SfButton/SfRating, ноль зависимостей.
 *
 * Ожидаемая структура $data:
 * [
 *   'title'       => string,
 *   'url'         => string,   // ссылка на data-driven страницу объекта
 *   'image'       => ['src'=>string,'alt'=>string,'width'=>int,'height'=>int],
 *   'price'       => string,
 *   'old_price'   => string|null,
 *   'rating'      => ['value'=>int,'max'=>int,'count'=>int]|null,
 *   'features'    => string[],
 *   'badge'       => string|null,
 *   'cta_text'    => string,
 *   'instanceId'  => string|null, // уникальность при повторах на странице
 * ]
 * $globalConfig: тема/навигация (пока не используется).
 */
$componentBaseId = basename(__DIR__); // "product-card"
$seed = $data['instanceId'] ?? ($data['url'] ?? $data['title'] ?? 'card');
$uid = $componentBaseId . '-' . substr(md5((string)$seed), 0, 6);
$title = htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8');
$url = htmlspecialchars($data['url'] ?? '#', ENT_QUOTES, 'UTF-8');
$img = $data['image'] ?? [];
$imgSrc = htmlspecialchars($img['src'] ?? '', ENT_QUOTES, 'UTF-8');
$imgAlt = htmlspecialchars($img['alt'] ?? $data['title'] ?? '', ENT_QUOTES, 'UTF-8');
$imgW = (int)($img['width'] ?? 300);
$imgH = (int)($img['height'] ?? 300);
$price = htmlspecialchars($data['price'] ?? '', ENT_QUOTES, 'UTF-8');
$oldPrice = isset($data['old_price']) ? htmlspecialchars($data['old_price'], ENT_QUOTES, 'UTF-8') : null;
$badge = isset($data['badge']) ? htmlspecialchars($data['badge'], ENT_QUOTES, 'UTF-8') : null;
$cta = htmlspecialchars($data['cta_text'] ?? 'В корзину', ENT_QUOTES, 'UTF-8');
$rating = $data['rating'] ?? null;
$features = $data['features'] ?? [];
?>
<article class="c-product-card" id="<?= $uid ?>" itemscope itemtype="https://schema.org/Product">
  <div class="c-product-card__media">
    <a class="c-product-card__imglink" href="<?= $url ?>">
      <img class="c-product-card__img"
        src="<?= $imgSrc ?>" alt="<?= $imgAlt ?>"
        width="<?= $imgW ?>" height="<?= $imgH ?>"
        loading="lazy" decoding="async" style="aspect-ratio:<?= $imgW ?>/<?= $imgH ?>"
        itemprop="image">
    </a>
    <?php if ($badge): ?>
      <span class="c-product-card__badge"><?= $badge ?></span>
    <?php endif; ?>
  </div>
  <div class="c-product-card__body">
    <h3 class="c-product-card__title" itemprop="name">
      <a href="<?= $url ?>"><?= $title ?></a>
    </h3>
    <?php if ($rating): ?>
      <p class="c-product-card__rating" aria-label="Рейтинг <?= (int)$rating['value'] ?> из <?= (int)$rating['max'] ?>">
        <span aria-hidden="true"><?= str_repeat('★', (int)$rating['value']) . str_repeat('☆', max(0, (int)$rating['max'] - (int)$rating['value'])) ?></span>
        <span class="c-product-card__count">(<?= (int)$rating['count'] ?>)</span>
      </p>
    <?php endif; ?>
    <?php if ($features): ?>
      <p class="c-product-card__features"><?= htmlspecialchars(implode(' • ', $features), ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <p class="c-product-card__price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
      <span><?= $price ?></span>
      <?php if ($oldPrice): ?><s class="c-product-card__old"><?= $oldPrice ?></s><?php endif; ?>
    </p>
    <a class="c-product-card__cta" href="<?= $url ?>"><?= $cta ?></a>
  </div>
</article>
