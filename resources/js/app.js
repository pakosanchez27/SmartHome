import './bootstrap';

import Alpine from 'alpinejs';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Banknote,
    Bell,
    BookOpen,
    CalendarDays,
    ChartColumn,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Clock,
    Ellipsis,
    Funnel,
    Heart,
    House,
    Info,
    LoaderCircle,
    Lock,
    LogOut,
    Menu,
    Minus,
    Package,
    PanelLeftClose,
    PanelLeftOpen,
    Plus,
    Receipt,
    RefreshCw,
    Search,
    Settings,
    ShoppingCart,
    Smartphone,
    Sparkles,
    Sprout,
    Target,
    Trash,
    TrendingDown,
    TrendingUp,
    TriangleExclamationPoint,
    User,
    Users,
    Utensils,
    Wallet,
    WifiOff,
    X,
    createIcons,
} from 'lucide';

window.Alpine = Alpine;

/*
 | Shell responsive (EP-02): sidebar colapsable persistente y hoja "Más" en móvil.
 */
Alpine.data('shell', () => ({
    collapsed: (() => {
        try {
            return window.localStorage.getItem('smarthome:sidebar') === 'collapsed';
        } catch {
            return false;
        }
    })(),
    moreOpen: false,
    moreLastFocused: null,

    toggleSidebar() {
        this.collapsed = !this.collapsed;

        try {
            window.localStorage.setItem('smarthome:sidebar', this.collapsed ? 'collapsed' : 'expanded');
        } catch {
            // Sin almacenamiento local el menú sigue funcionando en la sesión.
        }
    },

    openMore() {
        this.moreLastFocused = document.activeElement;
        this.moreOpen = true;
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => this.$refs.moreClose?.focus());
    },

    closeMore() {
        this.moreOpen = false;
        document.body.classList.remove('overflow-hidden');

        if (this.moreLastFocused instanceof HTMLElement) {
            this.moreLastFocused.focus();
            this.moreLastFocused = null;
        }
    },

    trapMore(event) {
        if (!this.moreOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.closeMore();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const panel = this.$refs.morePanel;
        const items = Array.from(
            panel?.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            ) ?? [],
        ).filter((element) => element.offsetParent !== null);

        if (!items.length) {
            event.preventDefault();
            panel?.focus();

            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && (document.activeElement === first || document.activeElement === panel)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
}));

/*
 | Diálogos y sheets (EP-02): foco atrapado, Esc, retorno de foco y bloqueo de scroll.
 | Se usa en <x-modal> y <x-drawer>.
 */
Alpine.data('overlay', (name = null) => ({
    open: false,
    name,
    lastFocused: null,

    init() {
        this.$watch('open', (value) => {
            document.body.classList.toggle('overflow-hidden', value);

            if (value) {
                this.lastFocused = document.activeElement;
                this.$nextTick(() => {
                    const items = this.focusables();
                    (items[0] ?? this.$refs.panel)?.focus();
                });
            } else if (this.lastFocused instanceof HTMLElement) {
                this.lastFocused.focus();
                this.lastFocused = null;
            }
        });
    },

    show() {
        this.open = true;
    },

    hide() {
        this.open = false;
    },

    focusables() {
        if (!this.$refs.panel) {
            return [];
        }

        return Array.from(
            this.$refs.panel.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            ),
        ).filter((element) => element.offsetParent !== null);
    },

    trap(event) {
        if (!this.open) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.hide();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const items = this.focusables();

        if (!items.length) {
            event.preventDefault();
            this.$refs.panel?.focus();

            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && (document.activeElement === first || document.activeElement === this.$refs.panel)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
}));

/*
 | Combobox accesible: búsqueda sin acentos, teclado y selección única.
 | Config: { options: [{ value, label, meta }], selected: option|null }
 */
Alpine.data('combobox', (config = {}) => ({
    open: false,
    query: '',
    options: config.options ?? [],
    selected: config.selected ?? null,
    highlighted: 0,

    get filtered() {
        const term = this.normalize(this.query);

        if (!term) {
            return this.options;
        }

        return this.options.filter((option) =>
            this.normalize(`${option.label} ${option.meta ?? ''}`).includes(term),
        );
    },

    normalize(value) {
        return String(value)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    },

    toggle() {
        if (this.open) {
            this.close();
        } else {
            this.openList();
        }
    },

    openList() {
        this.open = true;
        this.query = '';
        this.highlighted = Math.max(
            0,
            this.options.findIndex((option) => option.value === this.selected?.value),
        );
        this.$nextTick(() => this.$refs.search?.focus());
    },

    close() {
        this.open = false;
        this.query = '';
        this.$refs.trigger?.focus();
    },

    select(option) {
        this.selected = option;
        this.open = false;
        this.query = '';
        this.$nextTick(() => this.$refs.trigger?.focus());
    },

    move(step) {
        const total = this.filtered.length;

        if (!total) {
            return;
        }

        this.highlighted = (this.highlighted + step + total) % total;
    },

    choose() {
        const option = this.filtered[this.highlighted];

        if (option) {
            this.select(option);
        }
    },
}));

/*
 | Toasts no críticos. Nunca se usan para confirmar operaciones sensibles.
 */
Alpine.store('toasts', {
    items: [],
    sequence: 0,

    push(message, tone = 'success') {
        const id = ++this.sequence;
        this.items.push({ id, message, tone });
        window.setTimeout(() => this.dismiss(id), 5000);
    },

    dismiss(id) {
        this.items = this.items.filter((toast) => toast.id !== id);
    },
});

Alpine.start();

/*
 | Íconos Lucide usados por el shell y la biblioteca UI.
 | Al agregar un módulo, importa aquí sus íconos: <x-icon name="..." /> usa data-lucide.
 */
createIcons({
    icons: {
        ArrowLeft,
        ArrowRight,
        ArrowUpDown,
        Banknote,
        Bell,
        BookOpen,
        CalendarDays,
        ChartColumn,
        Check,
        ChevronDown,
        ChevronLeft,
        ChevronRight,
        CircleCheck,
        Clock,
        Ellipsis,
        Funnel,
        Heart,
        House,
        Info,
        LoaderCircle,
        Lock,
        LogOut,
        Menu,
        Minus,
        Package,
        PanelLeftClose,
        PanelLeftOpen,
        Plus,
        Receipt,
        RefreshCw,
        Search,
        Settings,
        ShoppingCart,
        Smartphone,
        Sparkles,
        Sprout,
        Target,
        Trash,
        TrendingDown,
        TrendingUp,
        TriangleExclamationPoint,
        User,
        Users,
        Utensils,
        Wallet,
        WifiOff,
        X,
    },
    attrs: {
        'stroke-width': 1.75,
    },
    inTemplates: true,
});
