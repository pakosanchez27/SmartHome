import './bootstrap';

import Alpine from 'alpinejs';
import { ArrowRight, CheckCircle2, Menu, ShieldCheck, ShoppingCart, Utensils, Wallet, createIcons } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

// Íconos Lucide usados en el shell. Se amplía por módulo en EP-02.
createIcons({
    icons: { ArrowRight, CheckCircle2, Menu, ShieldCheck, ShoppingCart, Utensils, Wallet },
});
