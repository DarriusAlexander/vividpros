import $ from 'jquery';
import { initInterframe } from '../lib/Interframe';
import { backgroundIframeUrl, iframeUrl } from '../constants/leadinConfig';

export function createIframe() {
  const container = $('#leadin-iframe-container');
  const $iframe = $(`<iframe id="leadin-iframe" src="${iframeUrl}"></iframe>`);
  initInterframe($iframe[0]);
  container.append($iframe);
}

export function createBackgroundIframe() {
  const iframe = $(
    `<iframe id="leadin-iframe" src="${backgroundIframeUrl}"></iframe>`
  );
  iframe.css({ display: 'none' });
  initInterframe(iframe[0]);
  $(document.body).append(iframe);
}
