import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser'
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;