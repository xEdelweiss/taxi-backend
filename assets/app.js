import 'leaflet/dist/leaflet.css';
import './styles/app.css';

import Alpine from 'alpinejs';
import L from 'leaflet';
import useUserClient from './scripts/useUserClient.js';
import useDriverClient from './scripts/useDriverClient.js';
import useAdminClient from './scripts/useAdminClient.js';
import TaxiUtils from './scripts/TaxiUtils.js';

window.Alpine = Alpine;
window.L = L;
window.TaxiUtils = TaxiUtils;

useUserClient();
useDriverClient();
useAdminClient();

Alpine.start()
