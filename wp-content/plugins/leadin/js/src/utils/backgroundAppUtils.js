import { createBackgroundIframe } from '../iframe/iframe';
import { initApp } from './appUtils';

export function initBackgroundApp(initFn) {
  function main() {
    createBackgroundIframe();
    initFn();
  }
  initApp(main);
}
