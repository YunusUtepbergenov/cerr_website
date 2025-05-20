import $ from 'jquery';
window.$ = $;
window.jQuery = $;

import '../css/vendor/bootstrap.min.css';
import '../css/style.css';
import '../css/plugins/fontawesome-5.css';

import swiperInit from './main.js';

document.addEventListener('DOMContentLoaded', () => {
  swiperInit.initSwipers();

  // Re-run after any Livewire component update
  if (window.Livewire) {
    window.Livewire.hook('message.processed', () => {
      swiperInit.initSwipers();
    });

    // ✅ This is CRUCIAL for full-page Livewire components
    window.addEventListener('livewire:navigated', () => {
      swiperInit.initSwipers();
    });
  }
});
