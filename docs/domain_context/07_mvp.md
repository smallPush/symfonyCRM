# 7. Minimum Viable Product (MVP)

In the development of a CRM using Domain-Driven Design, there is a risk of falling into over-modeling (over-engineering), designing complex concepts that do not add immediate value to the user.

To ensure early delivery of value, the model must be bounded in its first version (MVP). Below, the scope of the MVP for this domain is defined.

## 1. Essential Aggregates (In-Scope v1)

These Aggregate Roots form the operational core (Core Domain) of B2B sales and must be implemented:

1. **`Company` (and its internal entity `Contact`):** It is the heart of B2B. We need to store accounts and know who to talk to.
2. **`Deal`:** Represents the economic value that the CRM aims to track. Without opportunities, there is no commercial pipeline.
3. **`Activity`:** Sales are relationships. Logging notes, calls, and pending tasks is mandatory for the salesperson to use the tool in their day-to-day.
4. **`User`:** We need to identify who does what for basic auditing purposes and read projections (ActivityTimeline).

## 2. Technical Simplifications in the MVP

To accelerate development without sacrificing the hexagonal/DDD architecture, we will make these architectural decisions:

- **Synchronous Projections:** Read Projections (Read Models) will be updated synchronously or via SQL Views (Materialized Views) in the same relational database (PostgreSQL/MySQL), instead of implementing a complex event bus infrastructure (Kafka/RabbitMQ) and NoSQL databases for reading.
- **Basic Multitenancy:** If the system is SaaS, the `TenantId` will be managed as a filter (Data Isolation in Database per column) but a complex `Tenant` Aggregate will not be modeled to manage billing or limits. That is postponed to future versions.

## 3. Pieces Excluded at the Beginning (Out-of-Scope v1)

These concepts are valid, but they introduce unnecessary complexity in the initial phase and can be postponed:

### 1. The `Lead` Aggregate
- **Why it is left out:** Implementing a complete flow for capturing, qualifying, and converting Leads is complex. For v1, we assume that all commercial interest is registered directly as a `Company` with an associated `Contact` and a `Deal` is opened in the "Discovery" stage.
- **When to add it:** When the client needs to integrate web marketing forms or has such a high volume of prospects that mixing them with Accounts (`Company`) would "dirty" the main database.

### 2. `Address` Internal Entity inside `Company`
- **Why it is left out:** In many B2B software or service sales, the physical address is irrelevant to closing the deal.
- **Temporary solution:** Leave simple fields like `Country` and `City` at the root level of the company, and extract the `Address` entity later if complex shipping or billing logic is required.

### 3. `Product` or `PriceBook` Aggregate (Catalog)
- **Why it is left out:** Adding detailed product lines to Deals greatly complicates the UI and the domain.
- **Temporary solution:** The value of the opportunity is modeled as an estimated monetary amount (`EstimatedValue` in the Deal). The salesperson can detail what they are selling in a free text field or in a note (`DealNote`), without forcing the existence of a standardized catalog in v1.

### 4. Granular Permissions and Teams (Territories/Teams)
- **Why it is left out:** Visibility based on territories ("I only see companies in Spain, but the Manager sees all of them") requires very complex read projections and costly invariant checks.
- **Temporary solution:** In v1, visibility is global per Tenant. Every logged-in user in the company's instance can see all opportunities and accounts. The concept of `Owner` is introduced but purely informative, not restrictive.
