# SmartHome

Sistema Inteligente de Administración del Hogar: finanzas familiares, presupuesto quincenal, recetario, menú semanal, despensa, compras, planes y necesidades en una sola aplicación web responsive e instalable (PWA).

**Estado:** base técnica (EP-01). Todavía no hay módulos funcionales.
**Stack:** Laravel 12 · PHP 8.3 · MySQL · Blade · Tailwind CSS 4 · Alpine.js · Vite · Chart.js (previsto) · OpenRouter (previsto).

## Requisitos

- PHP 8.2 o superior con `pdo_mysql`, `mbstring`, `intl`, `bcmath`, `zip`.
- Composer 2.
- Node.js 20+ y npm.
- MySQL 8 (WampServer, Laragon o servidor local).

## Instalación

```bash
composer install
npm install
cp .env.example .env      # En Windows: Copy-Item .env.example .env
php artisan key:generate
```

Crea la base de datos y ajusta `.env`:

```sql
CREATE DATABASE smarthome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthome
DB_USERNAME=root
DB_PASSWORD=
```

Luego:

```bash
php artisan migrate
npm run build
php artisan serve
```

La aplicación queda en `http://localhost:8000`.

## Desarrollo

```bash
npm run dev        # Vite en modo watch
php artisan serve  # Servidor PHP
# Opcional, en otra terminal:
php artisan queue:work
php artisan schedule:work
```

## Verificación

```bash
php artisan test
./vendor/bin/pint --test     # En Windows: vendor\bin\pint --test
npm run build
php artisan route:list
```

## Estructura

```text
app/
  Actions/          # operaciones de negocio con efecto concreto
  Enums/            # estados y tipos estables
  Http/
    Controllers/    # controladores delgados
    Requests/       # validación y autorización de entrada
  Jobs/             # cola e idempotencia
  Models/           # entidades Eloquent
  Notifications/    # avisos verificables
  Policies/         # autorización por hogar/rol/privacidad
  Services/         # cálculos financieros, compras, inventario, IA
  Support/          # utilidades transversales
config/
  smarthome.php     # moneda, cantidades, uploads, presupuesto, IA
docs/               # backlog, dominio, reglas, ADR y trazabilidad
resources/
  css/              # tokens de identidad y Tailwind
  js/               # Alpine.js, Lucide y módulos JS
  views/            # Blade
```

## Documentación

- [Backlog maestro](docs/SMART_HOME_BACKLOG.md)
- [Glosario](docs/GLOSARIO.md)
- [Modelo de dominio](docs/MODELO_DOMINIO.md)
- [Reglas de negocio](docs/REGLAS_NEGOCIO.md)
- [Roles y privacidad](docs/ROLES_Y_PRIVACIDAD.md)
- [Matriz de trazabilidad](docs/MATRIZ_TRAZABILIDAD.md)
- [Alcance y versionado](docs/ALCANCE_Y_VERSIONADO.md)
- [Decisiones de arquitectura](docs/adr/README.md)
- [Instrucciones para agentes](AGENTS.md)

## Seguridad

- Nunca subas `.env`, claves de OpenRouter ni datos financieros reales al repositorio.
- `storage/app/private` no es accesible por URL; los archivos se sirven con autorización.
- La IA corre solo en backend y nunca escribe datos sin confirmación explícita.
- Los datos de demo deben ser ficticios.

## Próximos pasos

1. **EP-00:** cerrar ADR pendientes con el responsable del producto.
2. **EP-01:** completar verificación en el entorno local.
3. **EP-02:** sistema de diseño, layouts y componentes Blade.
