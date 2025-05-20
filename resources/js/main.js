
import Swiper from 'swiper/bundle';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const swiperInit = {
  initSwipers: function () {
    const initSwiper = (selector, options) => {
      const el = document.querySelector(selector);

      const instance = new Swiper(el, options);
      console.log(`Swiper initialized on ${selector}`, instance);

      setTimeout(() => {
        instance.update();
      }, 100);
    };

    initSwiper("#research", {
      slidesPerView: 4,
      spaceBetween: 30,
      grabCursor: true,
      navigation: {
        nextEl: "#researchNext",
        prevEl: "#researchPrev",
      },
      breakpoints: {
        1168: { slidesPerView: 4 },
        992: { slidesPerView: 3 },
        768: { slidesPerView: 2 },
        576: { slidesPerView: 1 },
        0: { slidesPerView: 1 },
      },
    });

    initSwiper("#eventsSwiper", {
      slidesPerView: 4,
      spaceBetween: 30,
      grabCursor: true,
      navigation: {
        nextEl: ".eventsNext",
        prevEl: ".eventsPrev",
      },
      breakpoints: {
        1168: { slidesPerView: 4 },
        992: { slidesPerView: 3 },
        768: { slidesPerView: 2 },
        576: { slidesPerView: 1 },
        0: { slidesPerView: 1 },
      },
    });

    initSwiper("#infoSwiper", {
      slidesPerView: 4,
      spaceBetween: 30,
      grabCursor: true,
      navigation: {
        nextEl: "#infoNext",
        prevEl: "#infoPrev",
      },
      breakpoints: {
        1168: { slidesPerView: 4 },
        992: { slidesPerView: 3 },
        768: { slidesPerView: 2 },
        576: { slidesPerView: 1 },
        0: { slidesPerView: 1 },
      },
    });
  }
};

export default swiperInit;