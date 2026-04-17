# 2. Entidades Internas por Agregado

En DDD, las entidades internas viven dentro de las fronteras (boundaries) de un Aggregate Root. Tienen identidad propia dentro de ese contexto, pero **no pueden ser accedidas ni modificadas directamente desde fuera del agregado**. Cualquier modificación debe pasar por el Aggregate Root para garantizar las reglas de negocio e invariantes.

A continuación, se detallan las entidades internas para cada uno de los Aggregate Roots principales definidos.

## Dentro del Agregado `Company`

### `Contact` (Contacto)
- **Qué representa:** Una persona que trabaja para la empresa o que es el punto de contacto dentro de la misma.
- **Identidad:** Tiene su propio ID (ej. UUID) para poder ser referenciado internamente o desde un Deal/Activity, pero su ciclo de vida depende de `Company`.
- **Por qué es entidad interna:** Un contacto no existe en el vacío en este CRM B2B. Si la empresa se elimina, sus contactos generalmente se archivan con ella. Modificar un contacto (ej. cambiar su rol a "Decision Maker") debe validarse a nivel de `Company` para asegurar que haya, por ejemplo, al menos un punto de contacto principal.

### `Address` (Dirección Física / Sede)
- **Qué representa:** Una ubicación física de la empresa (Sede Central, Oficina Regional, Almacén).
- **Identidad:** Puede tener un ID propio si la empresa maneja múltiples sedes y se necesita diferenciarlas para facturación o envíos.
- **Por qué es entidad interna:** Su existencia carece de sentido sin la empresa a la que pertenece.

## Dentro del Agregado `Deal`

### `DealNote` (Nota de Oportunidad)
- **Qué representa:** Un comentario rápido o apunte específico sobre la oportunidad en curso, menos formal que una `Activity` programada.
- **Identidad:** ID propio para poder ser editada/eliminada.
- **Por qué es entidad interna:** Es parte del estado interno del Deal. No tiene sentido buscar "todas las notas huérfanas"; siempre se leen junto con el Deal.

### `DealParticipant` (Participante de la Oportunidad)
- **Qué representa:** Un `Contact` (referenciado por su ID) que tiene un rol específico en este Deal (ej. "Influencer", "Decision Maker", "Legal Counsel").
- **Identidad:** ID propio de la relación o identidad local dentro del Deal.
- **Por qué es entidad interna:** El rol de una persona en un negocio específico es un detalle de ese Deal. Se modifica a través de `Deal->AddParticipant(contactId, role)`.

## Dentro del Agregado `Activity`

### `ActivityAttachment` (Adjunto de Actividad)
- **Qué representa:** Un archivo o documento (ej. un PDF de una propuesta) adjunto a un correo o a las notas de una reunión.
- **Identidad:** ID interno y referencia al archivo almacenado.
- **Por qué es entidad interna:** Si se elimina la actividad, los adjuntos también pierden su contexto principal en esta vista.

## ¿Qué NO son entidades internas?

Es común el error de modelar `Activity` como una entidad interna dentro de `Deal` o `Company`.
**¿Por qué es un error?**
- **Concurrencia:** Si un usuario añade una nota a la `Company` mientras otro actualiza la dirección comercial, se produciría un bloqueo en el agregado `Company`.
- **Paginación/Volumen:** Una `Company` puede tener miles de actividades a lo largo de los años. Cargar el agregado `Company` en memoria con todas sus actividades (o gestionar colecciones gigantes) es ineficiente.
- Por tanto, `Activity` es su propio Aggregate Root y simplemente hace una referencia (guarda el ID) hacia la `Company` o el `Deal`.
