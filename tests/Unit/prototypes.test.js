import test from 'node:test';
import assert from 'node:assert/strict';
import { registerPrototypes } from '../../resources/js/prototypes.js';

function flow(name) {
    const definitions = {};
    registerPrototypes({ data: (key, factory) => { definitions[key] = factory; } });
    const instance = definitions[name]();
    instance.messages = [];
    instance.$store = { toasts: { push: (message, tone) => instance.messages.push({ message, tone }) } };
    instance.$dispatch = () => {};
    return instance;
}

test('la compra requiere artículos marcados, no artículos pendientes', () => {
    const shopping = flow('shoppingList');
    shopping.items.forEach((item) => { item.bought = false; });
    shopping.confirm();
    assert.equal(shopping.confirmed, false);
    assert.equal(shopping.messages.at(-1).tone, 'error');

    shopping.items.forEach((item) => { item.bought = true; });
    shopping.confirm();
    assert.equal(shopping.confirmed, true);
});

test('la compra parcial conserva los artículos pendientes', () => {
    const shopping = flow('shoppingList');
    const before = shopping.items.filter((item) => !item.bought).map((item) => item.id);
    shopping.confirm();
    assert.equal(shopping.confirmed, true);
    assert.deepEqual(shopping.items.filter((item) => !item.bought).map((item) => item.id), before);
    shopping.confirm();
    assert.equal(shopping.messages.at(-1).tone, 'info');
});

test('la cantidad comprada debe ser positiva', () => {
    const shopping = flow('shoppingList');
    const item = shopping.items[0];
    shopping.updateQty(item, 0);
    assert.equal(item.qty, 1);
    shopping.updateQty(item, '2.5');
    assert.equal(item.qty, 2.5);
});

test('planear recetas no altera la despensa de demostración', () => {
    const planner = flow('mealPlanner');
    const before = structuredClone(planner.pantry);
    planner.picker = { dayId: 'mar', meal: 'lunch', recipeId: 'pollo', servings: 4 };
    planner.assign();
    assert.deepEqual(planner.pantry, before);
    assert.equal(planner.projected.find((item) => item.name === 'Pechuga de pollo').qty, 1000);
});

test('el menú valida receta y porciones antes de asignar', () => {
    const planner = flow('mealPlanner');
    planner.picker = { dayId: 'mar', meal: 'lunch', recipeId: 'pollo', servings: 0 };
    planner.assign();
    assert.equal(planner.days[1].meals.lunch, null);
    assert.equal(planner.messages.at(-1).tone, 'error');
    planner.picker.servings = '3';
    planner.assign();
    assert.equal(planner.days[1].meals.lunch.servings, 3);
    assert.equal(planner.projected.find((item) => item.name === 'Pechuga de pollo').qty, 750);
});

test('los ajustes de demostración no permiten existencias negativas', () => {
    const pantry = flow('pantryBoard');
    pantry.adjust(pantry.products[0], -100);
    assert.equal(pantry.products[0].stock, 0);
    pantry.setStock(pantry.products[0], -1);
    assert.equal(pantry.products[0].stock, 0);
});

test('los filtros de compras y despensa distinguen estados sin duplicar agotados', () => {
    const shopping = flow('shoppingList');
    shopping.filter = 'pending';
    assert.equal(shopping.visibleItems.length, shopping.totals.pendingCount);
    shopping.search = 'producto inexistente';
    assert.equal(shopping.visibleItems.length, 0);

    const pantry = flow('pantryBoard');
    pantry.filter = 'low';
    assert.equal(pantry.visible.length, pantry.lowCount);
    assert.ok(pantry.visible.every((product) => product.stock > 0));
    pantry.filter = 'out';
    assert.equal(pantry.visible.length, pantry.outCount);
});
