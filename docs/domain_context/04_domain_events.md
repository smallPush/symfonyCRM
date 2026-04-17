# 4. Eventos de Dominio

Los Eventos de Dominio (Domain Events) capturan ocurrencias importantes dentro del dominio en tiempo pasado. Permiten comunicar cambios de estado entre diferentes Aggregate Roots sin acoplarlos fuertemente y alimentan las proyecciones de lectura (CQRS) de manera asíncrona.

A continuación, se enumeran los eventos principales del CRM B2B agrupados por el Agregado que los emite.

## Relacionados con `Lead`

- **`LeadCaptured`**
  - **Cuándo:** Un nuevo prospecto entra al sistema (ej. formulario web, API).
  - **Payload:** `LeadId`, `Source`, `EmailAddress`, `Timestamp`.
  - **Reacción típica:** Enviar un email de bienvenida automático, notificar al equipo SDR.

- **`LeadQualified`**
  - **Cuándo:** Un representante de ventas marca el lead como calificado tras un contacto inicial positivo.
  - **Payload:** `LeadId`, `QualifyingUserId`, `Timestamp`.
  - **Reacción típica:** Actualizar proyecciones de marketing.

- **`LeadConverted`**
  - **Cuándo:** El lead se convierte en una cuenta real (`Company`), contacto y posible oportunidad (`Deal`).
  - **Payload:** `LeadId`, `GeneratedCompanyId`, `GeneratedContactId`, `GeneratedDealId`, `Timestamp`.
  - **Reacción típica:** Mover métricas de conversión, disparar flujos de onboarding.

## Relacionados con `Company` y Contactos

- **`CompanyRegistered`**
  - **Cuándo:** Se crea una nueva entidad comercial en el sistema.
  - **Payload:** `CompanyId`, `CompanyName`, `DomainName`, `OwnerUserId`.

- **`ContactAddedToCompany` / `ContactLinkedToCompany`**
  - **Cuándo:** Se registra una nueva persona bajo una empresa o un lead convertido crea un contacto.
  - **Payload:** `CompanyId`, `ContactId`, `EmailAddress`, `Role`.
  - **Reacción típica:** Enriquecer datos del contacto con servicios de terceros (Clearbit, Apollo).

## Relacionados con `Deal` (Pipeline)

- **`DealCreated`**
  - **Cuándo:** Se abre una nueva oportunidad comercial.
  - **Payload:** `DealId`, `CompanyId`, `EstimatedValue`, `OwnerUserId`.
  - **Reacción típica:** Notificar al manager si el `EstimatedValue` supera un umbral VIP.

- **`DealStageChanged`**
  - **Cuándo:** El Deal avanza (o retrocede) en el Pipeline.
  - **Payload:** `DealId`, `PreviousStage`, `NewStage`, `ChangedByUserId`.
  - **Reacción típica:** Si pasa a *Proposal*, quizás lanzar una automatización para generar un borrador de contrato; actualizar proyecciones de pipeline.

- **`DealWon`**
  - **Cuándo:** La oportunidad se cierra con éxito.
  - **Payload:** `DealId`, `CompanyId`, `FinalValue`, `ClosedByUserId`.
  - **Reacción típica:** Mandar señal a facturación/ERP, celebrar en Slack/Teams, cambiar el estado del cliente a "Activo".

- **`DealLost`**
  - **Cuándo:** La oportunidad se pierde.
  - **Payload:** `DealId`, `CompanyId`, `LostReason`, `ClosedByUserId`.
  - **Reacción típica:** Añadir a campañas de *nurturing* a largo plazo.

## Relacionados con `Activity`

- **`ActivityScheduled`**
  - **Cuándo:** Se planea una llamada, reunión o tarea futura.
  - **Payload:** `ActivityId`, `Type`, `TargetId`, `OwnerUserId`, `DueDate`.

- **`ActivityCompleted`**
  - **Cuándo:** El usuario marca la tarea/reunión como realizada.
  - **Payload:** `ActivityId`, `Type`, `TargetId`, `CompletedByUserId`.
  - **Reacción típica:** Actualizar el campo `LastActivityDate` en la `Company` o `Deal` afectado mediante consistencia eventual.
