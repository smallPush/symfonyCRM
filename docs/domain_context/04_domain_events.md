# 4. Domain Events

Domain Events capture important occurrences within the domain in the past tense. They allow communicating state changes between different Aggregate Roots without tightly coupling them and feed read projections (CQRS) asynchronously.

Below are the main events of the B2B CRM grouped by the Aggregate that emits them.

## Related to `Lead`

- **`LeadCaptured`**
  - **When:** A new prospect enters the system (e.g., web form, API).
  - **Payload:** `LeadId`, `Source`, `EmailAddress`, `Timestamp`.
  - **Typical reaction:** Send an automatic welcome email, notify the SDR team.

- **`LeadQualified`**
  - **When:** A sales representative marks the lead as qualified after a positive initial contact.
  - **Payload:** `LeadId`, `QualifyingUserId`, `Timestamp`.
  - **Typical reaction:** Update marketing projections.

- **`LeadConverted`**
  - **When:** The lead is converted into a real account (`Company`), contact, and possible opportunity (`Deal`).
  - **Payload:** `LeadId`, `GeneratedCompanyId`, `GeneratedContactId`, `GeneratedDealId`, `Timestamp`.
  - **Typical reaction:** Move conversion metrics, trigger onboarding flows.

## Related to `Company` and Contacts

- **`CompanyRegistered`**
  - **When:** A new business entity is created in the system.
  - **Payload:** `CompanyId`, `CompanyName`, `DomainName`, `OwnerUserId`.

- **`ContactAddedToCompany` / `ContactLinkedToCompany`**
  - **When:** A new person is registered under a company or a converted lead creates a contact.
  - **Payload:** `CompanyId`, `ContactId`, `EmailAddress`, `Role`.
  - **Typical reaction:** Enrich contact data with third-party services (Clearbit, Apollo).

## Related to `Deal` (Pipeline)

- **`DealCreated`**
  - **When:** A new commercial opportunity is opened.
  - **Payload:** `DealId`, `CompanyId`, `EstimatedValue`, `OwnerUserId`.
  - **Typical reaction:** Notify the manager if the `EstimatedValue` exceeds a VIP threshold.

- **`DealStageChanged`**
  - **When:** The Deal advances (or goes back) in the Pipeline.
  - **Payload:** `DealId`, `PreviousStage`, `NewStage`, `ChangedByUserId`.
  - **Typical reaction:** If it goes to *Proposal*, maybe launch an automation to generate a contract draft; update pipeline projections.

- **`DealWon`**
  - **When:** The opportunity is successfully closed.
  - **Payload:** `DealId`, `CompanyId`, `FinalValue`, `ClosedByUserId`.
  - **Typical reaction:** Send signal to billing/ERP, celebrate in Slack/Teams, change client status to "Active".

- **`DealLost`**
  - **When:** The opportunity is lost.
  - **Payload:** `DealId`, `CompanyId`, `LostReason`, `ClosedByUserId`.
  - **Typical reaction:** Add to long-term *nurturing* campaigns.

## Related to `Activity`

- **`ActivityScheduled`**
  - **When:** A future call, meeting, or task is planned.
  - **Payload:** `ActivityId`, `Type`, `TargetId`, `OwnerUserId`, `DueDate`.

- **`ActivityCompleted`**
  - **When:** The user marks the task/meeting as done.
  - **Payload:** `ActivityId`, `Type`, `TargetId`, `CompletedByUserId`.
  - **Typical reaction:** Update the `LastActivityDate` field in the affected `Company` or `Deal` through eventual consistency.
