import Alpine from 'alpinejs';
import './pwa.js';
import './tracking.js';
import { registerCartStore } from './cart.js';
import { registerWishlistStore } from './wishlist.js';

window.Alpine = Alpine;
registerCartStore(Alpine);
registerWishlistStore(Alpine);
Alpine.start();
