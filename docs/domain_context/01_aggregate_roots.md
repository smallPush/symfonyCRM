# 1. Aggregate Roots del Dominio

En un CRM B2B moderno basado en Domain-Driven Design (DDD), los Aggregate Roots (AR) son las entidades principales que garantizan la consistencia de los datos, marcan los límites transaccionales y exponen el comportamiento de negocio hacia las capas de aplicación.

A continuación se definen los Aggregate Roots clave para este dominio.

## 1. Company (Empresa / Cuenta)
- **Qué representa:** Una empresa u organización B2B con la que se tiene o se espera tener una relación comercial. En B2B, la *empresa* es el centro de las operaciones, no la persona física individual.
- **Por qué es aggregate root:** Encapsula toda la identidad corporativa y agrupa a todos los contactos (empleados) que trabajan en ella, así como sus direcciones. Modificar un contacto o añadir uno nuevo siempre se hace a través del contexto de la empresa para garantizar reglas (ej. no superar límites, asegurar un contacto principal).
- **Invariantes que protege:**
  - Debe tener un nombre comercial y, opcionalmente, un identificador fiscal o dominio web (ej. `acme.com`) que suele ser único por Tenant.
  - No puede haber dos contactos idénticos dentro de la misma empresa (mismo email).
- **Operaciones de negocio:**
  - `RegisterCompany()`
  - `UpdateCompanyProfile()`
  - `AddContact()`
  - `RemoveContact()`
  - `DesignatePrimaryContact()`

## 2. Lead (Prospecto)
- **Qué representa:** Un interés comercial en su fase más temprana, generado por campañas inbound o acciones outbound. Aún no está calificado ni se ha establecido una relación formal de venta.
- **Por qué es aggregate root:** Tiene un ciclo de vida efímero y totalmente independiente del resto de entidades. Un Lead entra, se trabaja, y al final del proceso se *convierte* (dando lugar a una `Company`, un `Contact` y un `Deal`) o se *descarta*.
- **Invariantes que protege:**
  - Un Lead debe contener un medio de contacto mínimo válido (un email, un teléfono, etc.).
  - Un Lead en estado `Converted` o `Disqualified` no puede retroceder a estado `New` sin una re-evaluación formal.
- **Operaciones de negocio:**
  - `CaptureLead()`
  - `QualifyLead()`
  - `DisqualifyLead()`
  - `Convert()`

## 3. Deal (Oportunidad / Opportunity)
- **Qué representa:** Una posible venta o negocio concreto que se está gestionando con una `Company`.
- **Por qué es aggregate root:** El pipeline comercial gira en torno a los Deals. Cada Deal avanza de forma independiente por las etapas de venta. Modificar un Deal (cambiar su valor, cambiar su etapa) no requiere bloquear la entidad `Company`.
- **Invariantes que protege:**
  - Todo Deal debe estar asociado al ID de una `Company`.
  - El importe económico o valor esperado (`EstimatedValue`) nunca puede ser negativo.
  - Una transición de etapa (ej. de *Discovery* a *Proposal*) debe seguir el flujo permitido por el Pipeline, no pudiendo saltarse estados si las reglas de negocio no lo permiten, o si faltan requisitos (ej. enviar propuesta obliga a tener un documento adjunto).
- **Operaciones de negocio:**
  - `OpenDeal()`
  - `ChangeStage()`
  - `UpdateEstimatedValue()`
  - `MarkAsWon()`
  - `MarkAsLost()`

## 4. Activity (Actividad / Tarea / Interacción)
- **Qué representa:** Cualquier acción registrada por un usuario comercial: una llamada, un correo electrónico, una reunión o una tarea pendiente relacionada con un Lead, una Company o un Deal.
- **Por qué es aggregate root:** Aunque conceptualmente pertenece a un cliente o a un negocio, a nivel técnico es mejor tratarla como un AR propio (que referencia por ID a los demás). Esto evita problemas de concurrencia: si dos comerciales añaden notas al mismo Deal al mismo tiempo, el Deal no debería bloquearse. Además, las actividades tienen su propio ciclo de vida (se pueden reasignar de dueño, posponer o completar de forma aislada).
- **Invariantes que protege:**
  - Toda actividad debe referenciar obligatoriamente a una entidad principal (`TargetId` y `TargetType` - ej. un Deal o un Lead) y a un propietario (`OwnerId`).
  - Si el tipo de actividad es `Meeting`, debe tener una fecha de inicio y una de fin consistentes (inicio <= fin).
- **Operaciones de negocio:**
  - `ScheduleActivity()`
  - `CompleteActivity()`
  - `RescheduleActivity()`
  - `ReassignActivity()`

## 5. User (Usuario / Representante Comercial)
- **Qué representa:** Un empleado que opera el CRM, como un Account Executive, un SDR (Sales Development Representative) o un Manager.
- **Por qué es aggregate root:** Controla el acceso, la pertenencia a equipos, los roles y la asignación de licencias de uso.
- **Invariantes que protege:**
  - El correo de acceso debe ser único por instalación/tenant.
  - Debe tener al menos un rol asignado para poder operar.
- **Operaciones de negocio:**
  - `RegisterUser()`
  - `AssignRole()`
  - `DeactivateUser()`

## 6. Tenant (Inquilino) - *Si aplica multitenancy*
- **Qué representa:** El espacio de trabajo u organización cliente que ha contratado nuestro software CRM.
- **Por qué es aggregate root:** Gestiona los límites de configuración globales del sistema (suscripción, límites de usuarios, configuración del Pipeline base).
- **Invariantes que protege:**
  - Un Tenant activo permite el acceso de sus usuarios; si se suspende, se bloquea la entrada.
- **Operaciones de negocio:**
  - `ProvisionTenant()`
  - `UpdateSubscription()`
  - `SuspendTenant()`
