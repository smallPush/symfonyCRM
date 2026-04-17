# 6. Proyecciones de Lectura (Read Models / CQRS)

En un sistema diseñado con DDD, el modelo de dominio está altamente optimizado para procesar reglas de negocio complejas (escritura/comandos). Sin embargo, las interfaces de usuario (como las de un CRM) necesitan vistas planas, desnormalizadas y agregadas que serían muy costosas de generar si se tiene que recorrer el árbol de Agregados.

Aquí es donde entran las **Proyecciones de Lectura** (Read Models). Escuchan los Eventos de Dominio y actualizan tablas o documentos optimizados para ser consultados rápidamente.

A continuación, las proyecciones más relevantes para el CRM B2B:

## 1. `PipelineBoardView` (Vista Kanban del Pipeline)
- **Propósito:** Mostrar al comercial sus oportunidades agrupadas por etapa.
- **Datos desnormalizados:**
  - `DealId`
  - `DealTitle`
  - `EstimatedValue`
  - `Stage`
  - `CompanyName` (evita tener que hacer un JOIN con la tabla de empresas en tiempo real)
  - `NextActivityDate` / `HasOverdueTask` (indicadores visuales)
- **Eventos que la actualizan:** `DealCreated`, `DealStageChanged`, `DealEstimatedValueUpdated`, `ActivityScheduled` (para el indicador).

## 2. `ActivityTimeline` (Historial de Actividad)
- **Propósito:** Renderizar el "feed" de todo lo que ha pasado con un cliente o en un deal sin hacer complejas uniones SQL.
- **Datos desnormalizados:** Cada fila es un `TimelineEntry` plano.
  - `Timestamp`
  - `Icon/Type` (Email, Call, Note, StatusChange)
  - `Title` (ej. "Llamada completada", "Movido a Proposal")
  - `ActorName` (Nombre del usuario que lo hizo)
  - `Excerpt` (Resumen del texto de la nota o email)
- **Eventos que la actualizan:** `ActivityCompleted`, `DealStageChanged`, `ContactAddedToCompany`. Una misma línea de tiempo puede mezclar actividades explícitas y eventos de sistema.

## 3. `CompanyDirectory` (Directorio de Cuentas)
- **Propósito:** Listado paginado y filtrable de las empresas.
- **Datos desnormalizados:**
  - ID, Nombre, Dominio
  - `PrimaryContactName`, `PrimaryContactEmail` (extraídos de la entidad interna de contacto para no cargar la colección)
  - `OpenDealsCount`, `TotalPipelineValue` (campos agregados y cacheados)
  - `LastInteractionDate`
- **Eventos que la actualizan:** `CompanyRegistered`, `DealCreated/Won/Lost`, `ActivityCompleted`.

## 4. `SalesDashboard` (Métricas Comerciales)
- **Propósito:** Mostrar los KPIs del equipo o de un usuario (Wins, Losses, Win Rate, Average Deal Size).
- **Datos desnormalizados:** Tablas de agregación por mes/usuario (OLAP ligero).
  - `YearMonth`
  - `UserId`
  - `TotalWonAmount`
  - `DealsClosedCount`
- **Eventos que la actualizan:** `DealWon`, `DealLost`. (Ojo: Si un Deal Won cambia su importe a posteriori por corrección, debe haber un evento `DealValueCorrected` que ajuste esta tabla).

## 5. `UpcomingTasks` (Lista de Tareas Diarias)
- **Propósito:** "Mi día" para un comercial.
- **Datos desnormalizados:**
  - Tareas pendientes ordenadas por fecha.
  - Contexto incluido (`CompanyName` o `LeadName`) para que no haya que hacer clic para saber a quién llamar.
- **Eventos que la actualizan:** `ActivityScheduled`, `ActivityCompleted`, `ActivityRescheduled`.

---
**Nota de Arquitectura:** En la v1 (MVP), estas proyecciones pueden ser simples vistas SQL (Views) o tablas de base de datos actualizadas sincrónicamente en la misma transacción que guarda el agregado. A medida que escale, se moverán a bases de datos especializadas (Elasticsearch para búsquedas, Redis para contadores, o bases de datos de documentos como MongoDB/DynamoDB) actualizadas por un bus de eventos asíncrono (RabbitMQ/Kafka).
