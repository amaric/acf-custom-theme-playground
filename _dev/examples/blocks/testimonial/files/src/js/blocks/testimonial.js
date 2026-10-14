// Runs on the front end and in the editor, where ACF injects (and re-renders)
// the block preview after load — hence init(scope) + render_block_preview.
const initTestimonial = (scope) => {
  scope.querySelectorAll('.testimonial-block-slider').forEach((slider) => {
    const swiperEl = slider.querySelector('.testimonial-block-swiper');

    if (!swiperEl || typeof Swiper === 'undefined') {
      return;
    }

    new Swiper(swiperEl, {
      slidesPerView: 1,
      spaceBetween: 24,
      speed: 600,
      keyboard: {
        enabled: true,
      },
      pagination: {
        el: slider.querySelector('.testimonial-block-pagination'),
        clickable: true,
      },
      navigation: {
        prevEl: slider.querySelector('.testimonial-block-prev'),
        nextEl: slider.querySelector('.testimonial-block-next'),
      },
    });
  });
};

// Block scripts print in <head>, before ACF's own JS, so look up `acf` only
// once the DOM is ready (still before the editor renders the previews).
document.addEventListener('DOMContentLoaded', () => {
  initTestimonial(document);

  if (window.acf) {
    window.acf.addAction('render_block_preview/type=testimonial', ($block) => initTestimonial($block[0]));
  }
});
