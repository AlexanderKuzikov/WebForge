<?php
/**
 * Компонент filters — сайдбар фильтров каталога.
 *
 * Идея: Filters Sidepanel из Storefront UI (MIT, vuestorefront/storefront-ui):
 * сортировка + группы на аккордеонах, внутри checkbox/radio + счётчики.
 * Код написан с нуля под WebForge: вместо SfAccordionItem — нативные
 * details/summary (минимум JS по философии), вместо SfChip/SfCheckbox —
 * обычные input + label. Работает без JS; script.js добавляет «очистить всё».
 *
 * Ожидаемая структура $data:
 * ['groups' => [['id'=>string,'title'=>string,'type'=>size|checkbox|radio,
 *   'open'=>bool,'details'=>[['id'=>string,'label'=>string,'value'=>string,'counter'=>int]]]],
 *  'instanceId' => string|null]
 */
$componentBaseId = basename(__DIR__); // "filters"
$seed = $data['instanceId'] ?? md5(json_encode($data['groups'] ?? []));
$asideId = $componentBaseId . '-' . substr(md5((string)$seed), 0, 6);
$groups = $data['groups'] ?? [];
?>
<aside class="c-filters" id="<?= $asideId ?>" aria-label="Фильтры каталога">
  <form class="c-filters__form" method="get" action="">
    <?php foreach ($groups as $gi => $g):
      $gTitle = htmlspecialchars($g['title'] ?? '', ENT_QUOTES, 'UTF-8');
      $gType = $g['type'] ?? 'checkbox';
      $gOpen = !empty($g['open']) ? ' open' : '';
      $gSafe = preg_replace('/[^a-z0-9_-]/i', '', $g['id'] ?? ('g' . $gi));
      $inputType = $gType === 'radio' ? 'radio' : 'checkbox';
      $inputName = $gType === 'radio' ? 'f_' . $gSafe : 'f_' . $gSafe . '[]';
    ?>
    <details class="c-filters__group"<?= $gOpen ?>>
      <summary class="c-filters__summary"><?= $gTitle ?></summary>
      <ul class="c-filters__list<?= $gType === 'size' ? ' c-filters__list--chips' : '' ?>">
        <?php foreach ($g['details'] ?? [] as $d):
          $dId = $asideId . '-' . preg_replace('/[^a-z0-9_-]/i', '', $d['id'] ?? uniqid());
          $dLabel = htmlspecialchars($d['label'] ?? '', ENT_QUOTES, 'UTF-8');
          $dValue = htmlspecialchars($d['value'] ?? '', ENT_QUOTES, 'UTF-8');
          $dCount = (int)($d['counter'] ?? 0);
          $disabled = $dCount === 0 ? ' disabled' : '';
        ?>
        <li class="c-filters__item">
          <label class="c-filters__label" for="<?= $dId ?>">
            <input class="c-filters__input" id="<?= $dId ?>" type="<?= $inputType ?>"
              name="<?= htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') ?>"
              value="<?= $dValue ?>"<?= $disabled ?>>
            <span class="c-filters__text"><?= $dLabel ?></span>
            <span class="c-filters__counter">(<?= $dCount ?>)</span>
          </label>
        </li>
        <?php endforeach; ?>
      </ul>
    </details>
    <?php endforeach; ?>
    <div class="c-filters__actions">
      <button class="c-filters__clear" type="reset">Очистить</button>
      <button class="c-filters__submit" type="submit">Показать товары</button>
    </div>
  </form>
</aside>
<script>document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('<?= $asideId ?>');
  if (el && window.initFilters) window.initFilters(el);
});</script>
