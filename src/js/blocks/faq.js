// Runs on the front end and in the editor, where ACF injects (and re-renders)
// the block preview after load — hence init(scope) + render_block_preview.
const initFaq = (scope) => {
  scope.querySelectorAll('.faq-block-item-question').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.closest('.faq-block-item');
      const isOpen = item.classList.toggle('is-open');

      button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });
};

// Block scripts print in <head>, before ACF's own JS, so look up `acf` only
// once the DOM is ready (still before the editor renders the previews).
document.addEventListener('DOMContentLoaded', () => {
  initFaq(document);

  if (window.acf) {
    window.acf.addAction('render_block_preview/type=faq', ($block) => initFaq($block[0]));
  }
});
