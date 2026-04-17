# 3. Value Objects

In the domain of a B2B CRM, Value Objects (VO) represent business concepts that lack their own identity and are defined solely by their attributes. They are immutable; if a change is needed, a new Value Object is created.

They encapsulate low-level business rules, preventing validation logic from spreading through application services or aggregates.

## 1. `Money`
- **Use:** Estimated amount of a `Deal` or annual billing of a `Company`.
- **Attributes:**
  - `Amount` (Decimal/Integer)
  - `Currency` (String, ISO 4217 e.g. "USD", "EUR")
- **Rules:**
  - `Amount` can never be negative in the context of an estimated Deal value.
  - Two `Money` objects with different `Currency` cannot be added without going through a conversion service (throws exception).

## 2. `EmailAddress`
- **Use:** Emails of a `Contact`, a `User`, or a `Lead`.
- **Attributes:**
  - `Value` (String)
- **Rules:**
  - Must comply with a regular expression or standard email validation.
  - (Optional) Must be lowercase for comparisons. Does not allow spaces.

## 3. `PhoneNumber`
- **Use:** Phone number of the `Company` or a `Contact`.
- **Attributes:**
  - `Number` (String)
  - `CountryCode` (String, optional but recommended)
- **Rules:**
  - Must contain a minimum of numeric digits and allowed characters (like '+').
  - Can be validated using the E.164 standard.

## 4. `DomainName` or `WebsiteUrl`
- **Use:** The website or corporate domain associated with a `Company` or `Lead`.
- **Attributes:**
  - `Value` (String)
- **Rules:**
  - Must be a valid domain or well-formed URL.
  - Used to group or identify contacts (e.g., if `john@acme.com` enters as a Lead, we know he belongs to the Company with domain `acme.com`).

## 5. `DateRange`
- **Use:** Date range for an `Activity` (like a meeting) or for a period of sales projections.
- **Attributes:**
  - `StartDate` (DateTime)
  - `EndDate` (DateTime)
- **Rules:**
  - `StartDate` must always be less than or equal to `EndDate`.
  - Allows calculating the total duration (`durationInMinutes()`).

## 6. States (State Objects or Typed Enums)
Complex states are also modeled as Value Objects or Enums, encapsulating the allowed transitions.

### `DealStage` (Opportunity Stage)
- **Possible values:** `Discovery`, `Qualified`, `Proposal`, `Negotiation`, `Won`, `Lost`.
- **Rules:**
  - Can encapsulate logic like `isTerminal()` (returns `true` for Won or Lost).

### `LeadStatus`
- **Possible values:** `New`, `Contacted`, `Qualified`, `Disqualified`, `Converted`.
- **Rules:**
  - `isActionable()` (returns `false` for Disqualified or Converted).

## 7. `TaxId` (Tax Identifier / VAT Number)
- **Use:** Legal identifier of a `Company`.
- **Attributes:**
  - `Value` (String)
- **Rules:**
  - Format validated according to the country (e.g., NIF in Spain, EIN in US).
  - Usually used in integrations with ERPs or external credit risk databases.
