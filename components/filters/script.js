/* components/filters/script.js — единственный JS фильтров.
   Работает только в контексте своего экземпляра (контракт WebForge).
   techdebt: фильтрация перезагрузкой формы (GET), без fetch/AJAX.
   Апгрейд — тот же контракт initFilters(el), внутрь добавить fetch. */
function initFilters(element) {
  const form = element.querySelector('form');
  if (!form) return;
  form.addEventListener('reset', () => {
    // нативный reset чистит инпуты; шлём чистый GET для статического каталога
    setTimeout(() => form.submit(), 0);
  });
}
window.initFilters = initFilters;
