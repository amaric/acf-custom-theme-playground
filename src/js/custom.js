document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = header ? header.querySelector('.site-header-toggle') : null;

  if (!toggle) {
    return;
  }

  const setOpen = (isOpen) => {
    header.classList.toggle('is-open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    toggle.setAttribute('aria-label', isOpen ? toggle.dataset.labelClose : toggle.dataset.labelOpen);
    document.documentElement.style.overflow = isOpen ? 'hidden' : '';
  };

  toggle.addEventListener('click', () => {
    setOpen(!header.classList.contains('is-open'));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && header.classList.contains('is-open')) {
      setOpen(false);
      toggle.focus();
    }
  });

  header.querySelectorAll('.site-header-overlay a').forEach((link) => {
    link.addEventListener('click', () => setOpen(false));
  });

  window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
    if (event.matches) {
      setOpen(false);
    }
  });
});
