import connectToChild from 'penpal/lib/connectToChild';
import Raven from './Raven';
import { syncRoute, leadinPageReload, leadinPageRedirect } from '../navigation';
import * as leadinConfig from '../constants/leadinConfig';
import {
  leadinClearQueryParam,
  getQueryParam,
  filterLeadinQueryParams,
} from '../utils/queryParams';
import { leadinGetPortalInfo } from '../utils/portalInfo';
import {
  leadinConnectPortal,
  leadinDisconnectPortal,
  skipSignup,
} from '../api/wordpressApi';

const methods = {
  leadinClearQueryParam,
  leadinPageReload,
  leadinPageRedirect,
  leadinGetPortalInfo,
  leadinConnectPortal,
  leadinDisconnectPortal,
  getLeadinConfig: () => leadinConfig,
  skipSignup,
};

const UNAUTHORIZED = 'unauthorized';
const REDIRECT = 'REDIRECT';
const hubspotBaseUrl = leadinConfig.hubspotBaseUrl;

function createConnectionToiFrame(iframe) {
  return connectToChild({
    // The iframe to which a connection should be made
    iframe,
    childOrigin: hubspotBaseUrl, // the plugin will reject all connections not coming from the iframe
    // Methods the parent is exposing to the child
    methods,
  });
}

export function initInterframe(iframe) {
  if (!iframe) return;

  if (!window.childFrameConnection) {
    window.childFrameConnection = createConnectionToiFrame(iframe);
    window.childFrameConnection.promise.catch(error =>
      Raven.captureException(error, {
        fingerprint: ['INTERFRAME_CONNECTION_ERROR'],
      })
    );
  }

  const redirectToLogin = event => {
    if (event.data === UNAUTHORIZED) {
      window.removeEventListener('message', redirectToLogin);
      iframe.src = leadinConfig.loginUrl;
    }
  };

  const handleUnauthorizedConnection = event => {
    if (event.data === UNAUTHORIZED) {
      const currentPage = getQueryParam('page');
      window.removeEventListener('message', handleUnauthorizedConnection);
      if (currentPage === 'leadin_settings') {
        leadinPageRedirect(leadinConfig.routes.leadin_settings['']);
      } else {
        leadinClearQueryParam();
        leadinPageReload();
      }
    }
  };

  const handleNavigation = event => {
    if (event.origin !== hubspotBaseUrl) return;
    try {
      const data = JSON.parse(event.data);
      if (data['leadin_sync_route']) {
        const route = data['leadin_sync_route'];
        const search = data['leadin_sync_search'];

        syncRoute(route, filterLeadinQueryParams(search));
      } else if (data['message'] === REDIRECT) {
        window.location.href = data['url'];
      }
    } catch (e) {
      // Error in parsing message
    }
  };

  const currentPage = getQueryParam('page');
  const triedConnectingPortal = getQueryParam('leadin_connect');
  if (currentPage !== 'leadin_settings' && currentPage !== 'leadin') {
    window.addEventListener('message', redirectToLogin);
  } else if (triedConnectingPortal) {
    window.addEventListener('message', handleUnauthorizedConnection);
  }

  window.addEventListener('message', handleNavigation);
}
