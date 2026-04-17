# 7. Producto Mínimo Viable (MVP)

En el desarrollo de un CRM usando Domain-Driven Design, existe el riesgo de caer en el sobre-modelado (over-engineering), diseñando conceptos complejos que no aportan valor inmediato al usuario.

Para asegurar una entrega temprana de valor, el modelo debe acotarse en su primera versión (MVP). A continuación, se define el alcance del MVP para este dominio.

## 1. Agregados Imprescindibles (In-Scope v1)

Estos Aggregate Roots forman el núcleo operativo (Core Domain) de las ventas B2B y deben estar implementados:

1. **`Company` (y su entidad interna `Contact`):** Es el corazón del B2B. Necesitamos almacenar cuentas y saber con quién hablar.
2. **`Deal`:** Representa el valor económico que el CRM pretende rastrear. Sin oportunidades, no hay pipeline comercial.
3. **`Activity`:** Las ventas son relaciones. Registrar notas, llamadas y tareas pendientes es obligatorio para que el comercial use la herramienta en su día a día.
4. **`User`:** Necesitamos identificar quién hace qué para fines básicos de auditoría y proyecciones de lectura (ActivityTimeline).

## 2. Simplificaciones Técnicas en el MVP

Para acelerar el desarrollo sin sacrificar la arquitectura hexagonal/DDD, tomaremos estas decisiones arquitectónicas:

- **Proyecciones Síncronas:** Las Proyecciones de Lectura (Read Models) se actualizarán de forma sincrónica o mediante Vistas SQL (Materialized Views) en la misma base de datos relacional (PostgreSQL/MySQL), en lugar de implementar una infraestructura compleja de bus de eventos (Kafka/RabbitMQ) y bases de datos NoSQL para lectura.
- **Multitenancy Básico:** Si el sistema es SaaS, el `TenantId` se gestionará como un filtro (Data Isolation en Base de Datos por columna) pero no se modelará un Agregado complejo de `Tenant` para gestionar billing o límites. Eso se pospone a versiones futuras.

## 3. Piezas Excluidas al Inicio (Out-of-Scope v1)

Estos conceptos son válidos, pero introducen complejidad innecesaria en la fase inicial y pueden postergarse:

### 1. El Agregado `Lead`
- **Por qué queda fuera:** Implementar un flujo completo de captura, cualificación y conversión de Leads es complejo. Para la v1, asumimos que todo interés comercial se da de alta directamente como una `Company` con un `Contact` asociado y se abre un `Deal` en la etapa "Discovery".
- **Cuando añadirlo:** Cuando el cliente necesite integrar formularios web de marketing o tenga un volumen tan alto de prospectos que mezclarlos con las Cuentas (`Company`) "ensucie" la base de datos principal.

### 2. Entidad interna `Address` dentro de `Company`
- **Por qué queda fuera:** En muchas ventas B2B de software o servicios, la dirección física es irrelevante para cerrar el trato.
- **Solución temporal:** Dejar campos simples como `Country` y `City` a nivel de la raíz de la empresa, y extraer la entidad `Address` más adelante si se requiere lógica de envíos o facturación compleja.

### 3. Agregado de `Product` o `PriceBook` (Catálogo)
- **Por qué queda fuera:** Añadir líneas de productos detalladas a los Deals complica enormemente el UI y el dominio.
- **Solución temporal:** El valor de la oportunidad se modela como un importe monetario estimado (`EstimatedValue` en el Deal). El comercial puede detallar qué están vendiendo en un campo de texto libre o en una nota (`DealNote`), sin forzar la existencia de un catálogo estandarizado en la v1.

### 4. Permisos Granulares y Equipos (Territories/Teams)
- **Por qué queda fuera:** La visibilidad basada en territorios ("Yo solo veo las empresas de España, pero el Manager ve todas") requiere proyecciones de lectura muy complejas y verificaciones de invariantes costosas.
- **Solución temporal:** En la v1, la visibilidad es global por Tenant. Todo usuario logueado en la instancia de la empresa puede ver todas las oportunidades y cuentas. Se introduce el concepto de `Owner` pero a nivel puramente informativo, no restrictivo.
