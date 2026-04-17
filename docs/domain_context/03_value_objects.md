# 3. Value Objects

En el dominio de un CRM B2B, los Value Objects (VO) representan conceptos del negocio que carecen de identidad propia y se definen únicamente por sus atributos. Son inmutables; si se necesita un cambio, se crea un nuevo Value Object.

Encapsulan reglas de negocio de bajo nivel, evitando que la lógica de validación se esparza por los servicios de aplicación o los agregados.

## 1. `Money`
- **Uso:** Importe estimado de un `Deal` o facturación anual de una `Company`.
- **Atributos:**
  - `Amount` (Decimal/Entero)
  - `Currency` (String, ISO 4217 ej. "USD", "EUR")
- **Reglas:**
  - `Amount` nunca puede ser negativo en el contexto de un valor estimado de Deal.
  - No se pueden sumar dos objetos `Money` con diferente `Currency` sin pasar por un servicio de conversión (lanza excepción).

## 2. `EmailAddress`
- **Uso:** Correos de un `Contact`, de un `User` o de un `Lead`.
- **Atributos:**
  - `Value` (String)
- **Reglas:**
  - Debe cumplir con una expresión regular o validación estándar de correo electrónico.
  - (Opcional) Debe estar en minúsculas para comparaciones. No permite espacios.

## 3. `PhoneNumber`
- **Uso:** Teléfono de la `Company` o de un `Contact`.
- **Atributos:**
  - `Number` (String)
  - `CountryCode` (String, opcional pero recomendado)
- **Reglas:**
  - Debe contener un mínimo de dígitos numéricos y caracteres permitidos (como '+').
  - Puede validarse usando el estándar E.164.

## 4. `DomainName` o `WebsiteUrl`
- **Uso:** El sitio web o dominio corporativo asociado a una `Company` o `Lead`.
- **Atributos:**
  - `Value` (String)
- **Reglas:**
  - Debe ser un dominio válido o URL bien formada.
  - Sirve para agrupar o identificar contactos (ej. si `john@acme.com` entra como Lead, sabemos que pertenece a la Company con dominio `acme.com`).

## 5. `DateRange`
- **Uso:** Rango de fechas para una `Activity` (como una reunión) o para un periodo de proyecciones comerciales.
- **Atributos:**
  - `StartDate` (DateTime)
  - `EndDate` (DateTime)
- **Reglas:**
  - `StartDate` siempre debe ser menor o igual a `EndDate`.
  - Permite calcular la duración total (`durationInMinutes()`).

## 6. Estados (State Objects o Enums tipados)
Los estados complejos también se modelan como Value Objects o Enums, encapsulando las transiciones permitidas.

### `DealStage` (Etapa de Oportunidad)
- **Valores posibles:** `Discovery`, `Qualified`, `Proposal`, `Negotiation`, `Won`, `Lost`.
- **Reglas:**
  - Puede encapsular lógica como `isTerminal()` (retorna `true` para Won o Lost).

### `LeadStatus`
- **Valores posibles:** `New`, `Contacted`, `Qualified`, `Disqualified`, `Converted`.
- **Reglas:**
  - `isActionable()` (retorna `false` para Disqualified o Converted).

## 7. `TaxId` (Identificador Fiscal / VAT Number)
- **Uso:** Identificador legal de una `Company`.
- **Atributos:**
  - `Value` (String)
- **Reglas:**
  - Formato validado según el país (ej. NIF en España, EIN en US).
  - Suele usarse en integraciones con ERPs o bases de datos externas de riesgo crediticio.
