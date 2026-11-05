/*
 | Prototipos funcionales EP-02.
 |
 | Simulan los cinco flujos prioritarios (gasto, presupuesto, menú, compra y
 | stock) con datos ficticios y estado en el navegador. NO guardan nada, no
 | llaman a OpenRouter y no representan movimientos reales. Sirven para validar
 | interacción, estados y responsive antes de implementar las épicas de dominio.
 */

const mxn = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
});

const money = (cents) => mxn.format((Number(cents) || 0) / 100);

const todayISO = () => {
    const date = new Date();
    const pad = (value) => String(value).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
};

const clone = (value) => JSON.parse(JSON.stringify(value));

const quantity = (value) => {
    const number = Number(value) || 0;

    return Number.isInteger(number) ? String(number) : number.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
};

export function registerPrototypes(Alpine) {
    /*
     | Flujo 1 · Registrar un gasto en tres pasos.
     */
    Alpine.data('expenseFlow', () => ({
        step: 1,
        saving: false,
        saved: null,
        accounts: [
            { value: 'cash', label: 'Efectivo', meta: '$2,450.00 disponibles' },
            { value: 'debit', label: 'Débito familiar', meta: '$12,340.00 disponibles' },
            { value: 'credit', label: 'Crédito', meta: '$4,500.00 disponibles' },
        ],
        categories: [
            { value: 'food', label: 'Alimentación', meta: 'Despensa y mercado' },
            { value: 'home', label: 'Hogar', meta: 'Servicios y mantenimiento' },
            { value: 'transport', label: 'Transporte', meta: 'Gasolina y traslados' },
            { value: 'health', label: 'Salud', meta: 'Farmacia y consultas' },
            { value: 'other', label: 'Otros', meta: 'Sin categoría específica' },
        ],
        form: {
            amount: '',
            concept: '',
            account: '',
            category: '',
            visibility: 'shared',
            date: '',
            note: '',
        },
        errors: {},

        init() {
            this.form.date = todayISO();
        },

        get amountCents() {
            const parsed = parseFloat(String(this.form.amount).replace(',', '.'));

            return Number.isFinite(parsed) ? Math.round(parsed * 100) : 0;
        },

        get accountLabel() {
            return this.accounts.find((account) => account.value === this.form.account)?.label ?? '—';
        },

        get categoryLabel() {
            return this.categories.find((category) => category.value === this.form.category)?.label ?? '—';
        },

        get visibilityLabel() {
            return this.form.visibility === 'shared' ? 'Compartido con el hogar' : 'Solo para mí';
        },

        fmt(cents) {
            return money(cents);
        },

        validateStep(step) {
            this.errors = {};

            if (step === 1) {
                if (this.amountCents <= 0) {
                    this.errors.amount = 'Ingresa un importe mayor que cero.';
                }

                if (this.form.concept.trim().length < 3) {
                    this.errors.concept = 'Describe el gasto en al menos 3 caracteres.';
                }

                if (!this.form.account) {
                    this.errors.account = 'Selecciona la cuenta de origen.';
                }
            }

            if (step === 2 && !this.form.category) {
                this.errors.category = 'Selecciona una categoría.';
            }

            return Object.keys(this.errors).length === 0;
        },

        next() {
            if (!this.validateStep(this.step)) {
                return;
            }

            this.step = Math.min(3, this.step + 1);
        },

        back() {
            this.step = Math.max(1, this.step - 1);
            this.errors = {};
        },

        submit() {
            if (!this.validateStep(1)) {
                this.step = 1;
                this.$store.toasts.push('Revisa los campos marcados.', 'error');

                return;
            }

            if (!this.validateStep(2)) {
                this.step = 2;

                return;
            }

            this.saving = true;

            window.setTimeout(() => {
                this.saving = false;
                this.saved = {
                    ...this.form,
                    amount: this.amountCents,
                    accountLabel: this.accountLabel,
                    categoryLabel: this.categoryLabel,
                };
                this.step = 4;
                this.$store.toasts.push('Gasto registrado en la demostración.', 'success');
            }, 700);
        },

        reset() {
            this.step = 1;
            this.saved = null;
            this.errors = {};
            this.$dispatch('reset-prototype-expense');
            this.form = {
                amount: '',
                concept: '',
                account: '',
                category: '',
                visibility: 'shared',
                date: todayISO(),
                note: '',
            };
        },
    }));

    /*
     | Flujo 2 · Presupuesto quincenal con filtros, paginación y estados.
     */
    Alpine.data('budgetBoard', () => ({
        period: 'q2',
        loading: false,
        category: 'all',
        page: 1,
        perPage: 4,
        categories: [
            { id: 'home', label: 'Hogar', planned: 600000, spent: 600000 },
            { id: 'food', label: 'Alimentación', planned: 450000, spent: 312000 },
            { id: 'transport', label: 'Transporte', planned: 150000, spent: 98000 },
            { id: 'services', label: 'Servicios', planned: 180000, spent: 180000 },
            { id: 'health', label: 'Salud', planned: 120000, spent: 45000 },
            { id: 'leisure', label: 'Ocio', planned: 80000, spent: 95000 },
        ],
        movements: [
            { id: 1, concept: 'Renta de noviembre', amount: 600000, type: 'expense', account: 'Débito familiar', date: '1 nov', category: 'Hogar', status: 'confirmed' },
            { id: 2, concept: 'Supermercado semanal', amount: 142000, type: 'expense', account: 'Débito familiar', date: '2 nov', category: 'Alimentación', status: 'confirmed' },
            { id: 3, concept: 'Pago de nómina', amount: 1200000, type: 'income', account: 'Débito familiar', date: '3 nov', category: 'Ingreso', status: 'confirmed' },
            { id: 4, concept: 'Gasolina', amount: 68000, type: 'expense', account: 'Efectivo', date: '4 nov', category: 'Transporte', status: 'confirmed' },
            { id: 5, concept: 'Internet y telefonía', amount: 89900, type: 'expense', account: 'Débito familiar', date: '5 nov', category: 'Servicios', status: 'pending' },
            { id: 6, concept: 'Farmacia', amount: 45000, type: 'expense', account: 'Efectivo', date: '5 nov', category: 'Salud', status: 'confirmed' },
            { id: 7, concept: 'Transferencia a ahorro', amount: 200000, type: 'transfer', account: 'Débito familiar', date: '5 nov', category: 'Ahorro', status: 'confirmed' },
            { id: 8, concept: 'Cine familiar', amount: 28000, type: 'expense', account: 'Efectivo', date: '6 nov', category: 'Ocio', status: 'confirmed' },
        ],

        get totals() {
            const planned = this.categories.reduce((sum, item) => sum + item.planned, 0);
            const spent = this.categories.reduce((sum, item) => sum + item.spent, 0);
            const income = this.movements
                .filter((movement) => movement.type === 'income')
                .reduce((sum, movement) => sum + movement.amount, 0);

            return {
                planned,
                spent,
                income,
                remaining: planned - spent,
                percent: planned > 0 ? Math.round((spent / planned) * 100) : 0,
            };
        },

        get filtered() {
            return this.category === 'all'
                ? this.movements
                : this.movements.filter((movement) => movement.category === this.categoryLabel);
        },

        get categoryLabel() {
            return this.categories.find((item) => item.id === this.category)?.label ?? '';
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get paged() {
            const start = (this.page - 1) * this.perPage;

            return this.filtered.slice(start, start + this.perPage);
        },

        fmt(cents) {
            return money(cents);
        },

        setCategory(category) {
            this.category = category;
            this.page = 1;
        },

        go(page) {
            this.page = Math.min(this.lastPage, Math.max(1, page));
        },

        refresh() {
            this.loading = true;

            window.setTimeout(() => {
                this.loading = false;
                this.$store.toasts.push('Presupuesto actualizado en la demostración.', 'info');
            }, 900);
        },
    }));

    /*
     | Flujo 3 · Menú semanal y necesidad estimada de ingredientes.
     */
    Alpine.data('mealPlanner', () => ({
        selectedDay: 'lun',
        days: [
            { id: 'lun', label: 'Lunes', date: '9 nov', meals: { breakfast: { recipeId: 'chilaquiles', servings: 2 }, lunch: null, dinner: null } },
            { id: 'mar', label: 'Martes', date: '10 nov', meals: { breakfast: null, lunch: null, dinner: null } },
            { id: 'mie', label: 'Miércoles', date: '11 nov', meals: { breakfast: null, lunch: null, dinner: null } },
            { id: 'jue', label: 'Jueves', date: '12 nov', meals: { breakfast: null, lunch: null, dinner: null } },
            { id: 'vie', label: 'Viernes', date: '13 nov', meals: { breakfast: null, lunch: null, dinner: null } },
            { id: 'sab', label: 'Sábado', date: '14 nov', meals: { breakfast: null, lunch: null, dinner: null } },
            { id: 'dom', label: 'Domingo', date: '15 nov', meals: { breakfast: null, lunch: null, dinner: null } },
        ],
        recipes: [
            {
                id: 'chilaquiles',
                name: 'Chilaquiles verdes',
                category: 'Desayuno',
                time: 25,
                base: 2,
                ingredients: [
                    { name: 'Tortilla de maíz', qty: 8, unit: 'piezas' },
                    { name: 'Salsa verde', qty: 250, unit: 'ml' },
                    { name: 'Queso fresco', qty: 100, unit: 'g' },
                ],
            },
            {
                id: 'sopa',
                name: 'Sopa de verduras',
                category: 'Comida',
                time: 35,
                base: 4,
                ingredients: [
                    { name: 'Zanahoria', qty: 3, unit: 'piezas' },
                    { name: 'Calabaza', qty: 2, unit: 'piezas' },
                    { name: 'Caldo de pollo', qty: 1, unit: 'litro' },
                ],
            },
            {
                id: 'pollo',
                name: 'Pollo al limón',
                category: 'Cena',
                time: 45,
                base: 2,
                ingredients: [
                    { name: 'Pechuga de pollo', qty: 500, unit: 'g' },
                    { name: 'Limón', qty: 3, unit: 'piezas' },
                    { name: 'Arroz', qty: 200, unit: 'g' },
                ],
            },
            {
                id: 'avena',
                name: 'Avena con fruta',
                category: 'Desayuno',
                time: 10,
                base: 2,
                ingredients: [
                    { name: 'Avena', qty: 100, unit: 'g' },
                    { name: 'Plátano', qty: 2, unit: 'piezas' },
                    { name: 'Leche', qty: 400, unit: 'ml' },
                ],
            },
        ],
        pantry: [
            { name: 'Tortilla de maíz', qty: 12, unit: 'piezas' },
            { name: 'Salsa verde', qty: 500, unit: 'ml' },
            { name: 'Queso fresco', qty: 150, unit: 'g' },
            { name: 'Arroz', qty: 800, unit: 'g' },
        ],
        picker: {
            dayId: null,
            meal: null,
            recipeId: '',
            servings: 2,
        },

        get plannedMeals() {
            return this.days.reduce(
                (total, day) => total + Object.values(day.meals).filter(Boolean).length,
                0,
            );
        },

        get projected() {
            const totals = new Map();

            this.days.forEach((day) => {
                Object.values(day.meals).forEach((meal) => {
                    if (!meal) {
                        return;
                    }

                    const recipe = this.recipes.find((item) => item.id === meal.recipeId);

                    if (!recipe) {
                        return;
                    }

                    const factor = meal.servings / recipe.base;

                    recipe.ingredients.forEach((ingredient) => {
                        const key = `${ingredient.name}|${ingredient.unit}`;
                        const current = totals.get(key) ?? {
                            name: ingredient.name,
                            unit: ingredient.unit,
                            qty: 0,
                        };

                        current.qty += ingredient.qty * factor;
                        totals.set(key, current);
                    });
                });
            });

            return Array.from(totals.values())
                .map((item) => {
                    const available = this.pantry
                        .filter((product) => product.name === item.name && product.unit === item.unit)
                        .reduce((sum, product) => sum + product.qty, 0);

                    return {
                        ...item,
                        available,
                        missing: Math.max(0, item.qty - available),
                    };
                })
                .sort((a, b) => a.name.localeCompare(b.name, 'es'));
        },

        get selectedRecipe() {
            return this.recipes.find((recipe) => recipe.id === this.picker.recipeId) ?? null;
        },

        mealLabel(meal) {
            return { breakfast: 'Desayuno', lunch: 'Comida', dinner: 'Cena' }[meal] ?? meal;
        },

        recipe(meal) {
            if (!meal) {
                return null;
            }

            return this.recipes.find((recipe) => recipe.id === meal.recipeId) ?? null;
        },

        openPicker(day, meal) {
            this.picker.dayId = day.id;
            this.picker.meal = meal;
            this.picker.recipeId = day.meals[meal]?.recipeId ?? this.recipes[0]?.id ?? '';
            this.picker.servings = day.meals[meal]?.servings ?? 2;
            this.$dispatch('open-modal', 'seleccionar-receta');
        },

        assign() {
            const day = this.days.find((item) => item.id === this.picker.dayId);

            const servings = Number(this.picker.servings);
            if (!day || !['breakfast', 'lunch', 'dinner'].includes(this.picker.meal)
                || !this.selectedRecipe || !Number.isInteger(servings) || servings < 1 || servings > 24) {
                this.$store.toasts.push('Selecciona una receta y de 1 a 24 porciones.', 'error');
                return;
            }

            day.meals[this.picker.meal] = {
                recipeId: this.picker.recipeId,
                servings,
            };

            this.$dispatch('close-modal', 'seleccionar-receta');
            this.$store.toasts.push('Receta agregada al menú. No se descontó despensa.', 'success');
        },

        remove(day, meal) {
            day.meals[meal] = null;
            this.$store.toasts.push('Platillo quitado del menú.', 'info');
        },

        duplicate(day) {
            const index = this.days.findIndex((item) => item.id === day.id);

            if (index < 0 || index === this.days.length - 1) {
                this.$store.toasts.push('No hay un día siguiente para duplicar.', 'error');

                return;
            }

            this.days[index + 1].meals = clone(day.meals);
            this.$store.toasts.push(`Menú de ${this.days[index + 1].label} actualizado.`, 'success');
        },

        clear(day) {
            day.meals = { breakfast: null, lunch: null, dinner: null };
            this.$store.toasts.push(`Menú de ${day.label} vaciado.`, 'info');
        },

        quantity(value) {
            return quantity(value);
        },
    }));

    /*
     | Flujo 4 · Lista de compras y confirmación de compra.
     */
    Alpine.data('shoppingList', () => ({
        items: [
            { id: 1, name: 'Tortillas de maíz', qty: 1, unit: 'kg', price: 2400, bought: true, note: 'Faltan 0.5 kg según despensa', needed: true },
            { id: 2, name: 'Queso fresco', qty: 0.5, unit: 'kg', price: 18000, bought: false, note: 'Sin existencia registrada', needed: true },
            { id: 3, name: 'Salsa verde', qty: 1, unit: 'litro', price: 3200, bought: false, note: 'Ya tienes 500 ml en despensa', needed: false },
            { id: 4, name: 'Pechuga de pollo', qty: 1, unit: 'kg', price: 16500, bought: false, note: 'Faltan 500 g para el menú', needed: true },
            { id: 5, name: 'Plátano', qty: 1, unit: 'kg', price: 2200, bought: true, note: 'Agregado manualmente', needed: false },
            { id: 6, name: 'Frijoles', qty: 1, unit: 'kg', price: 4500, bought: false, note: 'Agregado manualmente', needed: false },
        ],
        search: '',
        filter: 'all',
        onlyNeeded: false,
        confirmed: false,
        account: 'debit',

        get visibleItems() {
            const term = this.search.trim().toLowerCase();

            return this.items.filter((item) => {
                if (term && !item.name.toLowerCase().includes(term)) {
                    return false;
                }

                if (this.filter === 'pending' && item.bought) {
                    return false;
                }

                if (this.filter === 'bought' && !item.bought) {
                    return false;
                }

                return !(this.onlyNeeded && !item.needed);
            });
        },

        get totals() {
            return this.items.reduce(
                (totals, item) => {
                    const subtotal = Math.round(item.qty * item.price);
                    totals.all += subtotal;

                    if (item.bought) {
                        totals.bought += subtotal;
                        totals.boughtCount += 1;
                    } else {
                        totals.pending += subtotal;
                        totals.pendingCount += 1;
                    }

                    return totals;
                },
                { all: 0, bought: 0, pending: 0, boughtCount: 0, pendingCount: 0 },
            );
        },

        fmt(cents) {
            return money(cents);
        },

        toggle(item) {
            item.bought = !item.bought;

            if (item.bought) {
                this.$store.toasts.push('Artículo marcado. Aún no se registra el egreso.', 'info');
            }
        },

        updateQty(item, value) {
            const amount = Number(value);
            if (!Number.isFinite(amount) || amount <= 0) {
                this.$store.toasts.push('La cantidad debe ser mayor que cero.', 'error');
                return;
            }
            item.qty = amount;
        },

        confirm() {
            if (this.confirmed) {
                this.$store.toasts.push('Esta compra de demostración ya fue confirmada.', 'info');

                return;
            }

            if (this.totals.boughtCount === 0) {
                this.$store.toasts.push('Selecciona al menos un artículo comprado para confirmar.', 'error');

                return;
            }

            this.confirmed = true;
            this.$dispatch('close-modal', 'confirmar-compra');
            this.$store.toasts.push('Confirmación simulada: no se registraron egresos ni existencias.', 'success');
        },

        reset() {
            this.confirmed = false;
            this.items = this.items.map((item) => ({ ...item, bought: false }));
        },
    }));

    /*
     | Flujo 5 · Despensa, conteo rápido y ajuste de existencias.
     */
    Alpine.data('pantryBoard', () => ({
        products: [
            { id: 1, name: 'Tortilla de maíz', category: 'Abarrotes', stock: 0.5, min: 1, unit: 'kg', location: 'Alacena', expiresAt: '12 nov' },
            { id: 2, name: 'Queso fresco', category: 'Refrigerados', stock: 0, min: 0.25, unit: 'kg', location: 'Refrigerador', expiresAt: '8 nov' },
            { id: 3, name: 'Salsa verde', category: 'Abarrotes', stock: 0.5, min: 0.5, unit: 'litro', location: 'Alacena', expiresAt: null },
            { id: 4, name: 'Pechuga de pollo', category: 'Congelados', stock: 0.75, min: 0.5, unit: 'kg', location: 'Congelador', expiresAt: '20 nov' },
            { id: 5, name: 'Arroz', category: 'Abarrotes', stock: 0.8, min: 0.5, unit: 'kg', location: 'Alacena', expiresAt: null },
            { id: 6, name: 'Leche', category: 'Refrigerados', stock: 1.2, min: 1, unit: 'litro', location: 'Refrigerador', expiresAt: '9 nov' },
        ],
        search: '',
        filter: 'all',
        countMode: false,

        get visible() {
            const term = this.search.trim().toLowerCase();

            return this.products.filter((product) => {
                if (term && !`${product.name} ${product.category}`.toLowerCase().includes(term)) {
                    return false;
                }

                if (this.filter === 'low') {
                    return product.stock > 0 && product.stock <= product.min;
                }

                if (this.filter === 'out') {
                    return product.stock <= 0;
                }

                return true;
            });
        },

        get lowCount() {
            return this.products.filter((product) => product.stock > 0 && product.stock <= product.min).length;
        },

        get outCount() {
            return this.products.filter((product) => product.stock <= 0).length;
        },

        get totalProducts() {
            return this.products.length;
        },

        status(product) {
            if (product.stock <= 0) {
                return 'out';
            }

            return product.stock <= product.min ? 'low' : 'ok';
        },

        percent(product) {
            const max = Math.max(product.min * 2, product.stock, 1);

            return Math.min(100, Math.round((product.stock / max) * 100));
        },

        quantity(value) {
            return quantity(value);
        },

        adjust(product, delta) {
            product.stock = Math.max(0, Math.round((product.stock + delta) * 100) / 100);
            this.$store.toasts.push(`Existencia de ${product.name} actualizada (demostración).`, 'success');
        },

        setStock(product, value) {
            product.stock = Math.max(0, Math.round((Number(value) || 0) * 100) / 100);
        },

        restock(product) {
            product.stock = Math.round((product.stock + Math.max(product.min, 1)) * 100) / 100;
            this.$store.toasts.push(`Se agregó existencia a ${product.name} (demostración).`, 'success');
        },

        toggleCount() {
            this.countMode = !this.countMode;
        },
    }));
}
