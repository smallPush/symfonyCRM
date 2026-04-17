# 5. Relationships between Aggregates

In a correct DDD design, Aggregate Roots should not keep references to other memory objects (e.g., `Deal->getCompany()->getName()`). Instead, they are related through **ID references**. This ensures that each Aggregate can be persisted independently and reduces database locks.

## 1. ID Reference Relationships (Decoupled)

### `Deal` -> `Company`
- **Implementation:** `Deal` stores a `CompanyId` (Value Object or primitive), not a `Company` object.
- **Advantage:** If the company name is updated, it does not affect the transactions that are modifying the Deal.

### `Activity` -> `Target (Deal, Company, Lead)`
- **Implementation:** Activities are polymorphic. They have a `TargetId` and a `TargetType` (e.g., `Deal`, `Company`).
- **Advantage:** Inserting a note or meeting does not lock the Deal or Company row in the database.

### Any Aggregate -> `User`
- **Implementation:** `OwnerId`, `CreatedById`, `ClosedById`.
- **Advantage:** User data (name, avatar) changes very rarely. In the write database we only store the ID; the join with user data is done in the read projections.

## 2. Immediate Consistency vs Eventual Consistency

The transaction boundary in DDD is the Aggregate. Everything that happens *inside* an Aggregate must have **immediate consistency** (ACID Transaction). What affects *other* Aggregates is handled through events and **eventual consistency**.

### Immediate Consistency (Inside the Aggregate)
- **Example:** When adding a `Contact` to a `Company` and marking it as `Primary`, the rule "there can only be one Primary Contact" must be evaluated and saved in the same database transaction.
- **Example:** When changing a `Deal` to `Won` status, the `ClosedDate` field must be set simultaneously.

### Eventual Consistency (Between Aggregates)
- **Example: `LastActivityDate` in `Company`**
  - **Problem:** We want to know when was the last time we interacted with an account for reports.
  - **Eventual solution:** When an `Activity` is completed, it emits the `ActivityCompleted` event. An *Event Handler* listens to this event, loads the corresponding `Company` (via `CompanyId`), and updates its `LastActivityDate` field. This happens milliseconds later and asynchronously.
- **Example: Move to "Customer"**
  - **Problem:** When the first `Deal` is marked as `Won`, the `Company` should change its status from `Prospect` to `Customer`.
  - **Eventual solution:** The `DealWon` event triggers a process that sends an `UpdateCompanyStatus` command to the `Company` aggregate.
- **Example: User Deletion**
  - **Problem:** If a `User` is deactivated, what happens to their Deals?
  - **Eventual solution:** The `UserDeactivated` event triggers a batch reassignment (perhaps asynchronous) that sends the `ReassignDeal` command to all deals where `OwnerId == DeactivatedUserId`. It is not attempted to do everything in a macro-transaction.
