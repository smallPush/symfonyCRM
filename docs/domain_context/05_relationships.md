# 5. Relaciones entre Agregados

En un diseño correcto de DDD, los Aggregate Roots no deben guardar referencias a otros objetos de memoria (ej. `Deal->getCompany()->getName()`). En su lugar, se relacionan mediante **referencias por ID**. Esto asegura que cada Agregado pueda persistirse independientemente y reduce los bloqueos de base de datos.

## 1. Relaciones por Referencia de ID (Desacopladas)

### `Deal` -> `Company`
- **Implementación:** `Deal` guarda un `CompanyId` (Value Object o primitivo), no un objeto `Company`.
- **Ventaja:** Si se actualiza el nombre de la empresa, no afecta a las transacciones que están modificando el Deal.

### `Activity` -> `Target (Deal, Company, Lead)`
- **Implementación:** Las actividades son polimórficas. Tienen un `TargetId` y un `TargetType` (ej. `Deal`, `Company`).
- **Ventaja:** La inserción de una nota o reunión no bloquea la fila del Deal o la Empresa en la base de datos.

### Cualquier Agregado -> `User`
- **Implementación:** `OwnerId`, `CreatedById`, `ClosedById`.
- **Ventaja:** Los datos de usuario (nombre, avatar) cambian muy rara vez. En la base de datos de escritura solo guardamos el ID; la unión con los datos del usuario se hace en las proyecciones de lectura.

## 2. Consistencia Inmediata vs Consistencia Eventual

El límite de transacción en DDD es el Agregado. Todo lo que ocurre *dentro* de un Agregado debe tener **consistencia inmediata** (Transacción ACID). Lo que afecta a *otros* Agregados se maneja mediante eventos y **consistencia eventual**.

### Consistencia Inmediata (Dentro del Agregado)
- **Ejemplo:** Al añadir un `Contact` a una `Company` y marcarlo como `Primary`, la regla de "solo puede haber un Primary Contact" debe evaluarse y guardarse en la misma transacción de base de datos.
- **Ejemplo:** Al cambiar un `Deal` a estado `Won`, el campo `ClosedDate` debe setearse simultáneamente.

### Consistencia Eventual (Entre Agregados)
- **Ejemplo: `LastActivityDate` en `Company`**
  - **Problema:** Queremos saber cuándo fue la última vez que interactuamos con una cuenta para reportes.
  - **Solución eventual:** Cuando una `Activity` se completa, emite el evento `ActivityCompleted`. Un *Event Handler* escucha este evento, carga la `Company` correspondiente (vía `CompanyId`) y actualiza su campo `LastActivityDate`. Esto ocurre milisegundos después y de forma asíncrona.
- **Ejemplo: Mover a "Cliente"**
  - **Problema:** Cuando el primer `Deal` se marca como `Won`, la `Company` debería cambiar su status de `Prospect` a `Customer`.
  - **Solución eventual:** El evento `DealWon` dispara un proceso que envía un comando `UpdateCompanyStatus` al agregado `Company`.
- **Ejemplo: Eliminación de un Usuario**
  - **Problema:** Si se da de baja a un `User`, ¿qué pasa con sus Deals?
  - **Solución eventual:** El evento `UserDeactivated` dispara una reasignación en lote (quizás asíncrona) que envía el comando `ReassignDeal` a todos los deals donde `OwnerId == DeactivatedUserId`. No se intenta hacer todo en una macro-transacción.
