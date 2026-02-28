> [!NOTE]
> **Sobre este Estándar de Calidad:**
> Este proyecto adopta la convención "Código Bilingüe" (Lógica en Inglés / Contexto en Español) para equilibrar los estándares globales de la industria con la facilidad de mantenimiento en equipos hispanohablantes.
>
> Estas reglas están integradas en la configuración de las herramientas de CI (PHPStan, PHP-CS-Fixer). Aunque recomendamos seguirlas para garantizar la consistencia y escalabilidad del skeleton, son totalmente adaptables a las guías de estilo de tu propia organización.

# Guía de Calidad de Código Bilingüe con Interfaz Localizada

Esta guía define el estándar "código bilingüe con interfaz localizada". El objetivo es mantener el código fuente al 100% en inglés como estándar (naming, clases, métodos, archivos) mientras la documentación técnica (PHPDoc) y toda la interfaz de usuario (mensajes, vistas, notificaciones) se entregan en español neutro.

## Principios

- **Código 100% en inglés:** Nombres de clases, métodos, variables, archivos y rutas internas.
- **Documentación en español técnico:** PHPDoc y comentarios explicativos complejos.
- **Interfaz localizada en español:** Respuestas de API (JSON), textos de vistas y notificaciones.

## Tipado y Estándares (PSR-12)

- Siempre declarar tipos estrictos (`declare(strict_types=1);`) al inicio de cada archivo PHP.
- Siempre declarar tipos en parámetros de métodos, propiedades y retornos.
- Aplicar PSR-12 utilizando `php-cs-fixer`.

## Checklist por archivo

- [ ] `declare(strict_types=1);` presente.
- [ ] Nombres en inglés (Clases, Métodos, Variables).
- [ ] PHPDoc en español técnico con tipos explícitos (`@param`, `@return`, `@throws`).
- [ ] Mensajes de respuesta al cliente (JSON/Vistas) en español.
- [ ] Tipos explícitos en propiedades y métodos; uso de `void` en retornos cuando aplique.
- [ ] Pasa validación de estilo: `composer lint`.
- [ ] Pasa análisis estático: `composer test:stan`.
- [ ] Pruebas unitarias/feature actualizadas y pasando: `composer test:unit`.

## Comandos de Calidad (CI Local)

Ejecuta estos comandos antes de realizar un commit para asegurar la calidad del código.

### 1. Estilo de Código (Linting)

Utilizamos **PHP-CS-Fixer** para asegurar el cumplimiento de PSR-12.

```bash
# Verificar estilo (sin cambios)
composer lint

# Corregir estilo automáticamente
composer lint:fix
```

### 2. Análisis Estático

Utilizamos **PHPStan** (nivel max) para detectar errores de tipos y lógica.

```bash
composer test:stan
```

### 3. Pruebas Automatizadas

Utilizamos **PHPUnit** para pruebas unitarias y de integración.

```bash
composer test:unit
```

### 4. Refactorización Automática

Utilizamos **Rector** para actualizaciones de código y mejoras de calidad.

```bash
# Verificar cambios sugeridos
composer rector:dry

# Aplicar cambios
composer rector:fix
```

## Guía de Documentación (PHPDoc)

Actúa como un Arquitecto de Software. La documentación debe ser concisa, en español técnico, y útil para el IDE.

**Estructura requerida:**

```php
/**
 * Descripción clara de la clase o método en español.
 *
 * @param Type $param Descripción del parámetro.
 * @return ReturnType Descripción del valor de retorno.
 * @throws ExceptionType Descripción de la excepción (si aplica).
 */
```

**Ejemplo:**

```php
/**
 * Crea un nuevo usuario en la base de datos.
 *
 * Valida los datos de entrada y persiste el registro si no existe duplicidad.
 *
 * @param array<string, mixed> $data Datos del usuario (nombre, email, etc.).
 * @return User Instancia del usuario creado.
 * @throws ValidationException Si los datos no son válidos.
 */
public function create(array $data): User
{
    // ...
}
```

## Uso

- Al crear o refactorizar archivos, sigue este documento como referencia práctica.
- Mantén el naming en inglés y la documentación/interfaz en español de forma consistente.
