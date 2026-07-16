import Alpine from 'alpinejs';
import './pwa.js';
import './tracking.js';
import { registerCartStore } from './cart.js';

window.Alpine = Alpine;
registerCartStore(Alpine);
Alpine.start();
