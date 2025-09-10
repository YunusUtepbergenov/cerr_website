// import $ from 'jquery';
// window.$ = $;
// window.jQuery = $;

import '../js/vendor/jquery.min.js';
import '../css/vendor/bootstrap.min.css';
import '../js/plugins/audio.js';
import '../js/plugins/magnific-popup.js';
import '../js/helper.js';

import swiperInit from './main.js';

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
