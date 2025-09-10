import swiperInit from './main.js';
import '../js/helper.js';

document.addEventListener('DOMContentLoaded', () => {
  swiperInit.initSwipers();

  if (window.Livewire) {
    window.Livewire.hook('message.processed', () => {
      swiperInit.initSwipers();
    });

    window.addEventListener('livewire:navigated', () => {
      swiperInit.initSwipers();
    });
  }
});
