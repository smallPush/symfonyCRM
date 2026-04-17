# 6. Read Projections (Read Models / CQRS)

In a system designed with DDD, the domain model is highly optimized to process complex business rules (write/commands). However, user interfaces (like those of a CRM) need flat, denormalized, and aggregated views that would be very expensive to generate if you had to traverse the Aggregate tree.

This is where **Read Projections** (Read Models) come in. They listen to Domain Events and update tables or documents optimized to be queried quickly.

Below are the most relevant projections for the B2B CRM:

## 1. `PipelineBoardView` (Kanban Pipeline View)
- **Purpose:** Show the salesperson their opportunities grouped by stage.
- **Denormalized data:**
  - `DealId`
  - `DealTitle`
  - `EstimatedValue`
  - `Stage`
  - `CompanyName` (avoids having to do a JOIN with the companies table in real time)
  - `NextActivityDate` / `HasOverdueTask` (visual indicators)
- **Events that update it:** `DealCreated`, `DealStageChanged`, `DealEstimatedValueUpdated`, `ActivityScheduled` (for the indicator).

## 2. `ActivityTimeline` (Activity History)
- **Purpose:** Render the "feed" of everything that has happened with a client or in a deal without making complex SQL joins.
- **Denormalized data:** Each row is a flat `TimelineEntry`.
  - `Timestamp`
  - `Icon/Type` (Email, Call, Note, StatusChange)
  - `Title` (e.g., "Call completed", "Moved to Proposal")
  - `ActorName` (Name of the user who did it)
  - `Excerpt` (Summary of the note or email text)
- **Events that update it:** `ActivityCompleted`, `DealStageChanged`, `ContactAddedToCompany`. The same timeline can mix explicit activities and system events.

## 3. `CompanyDirectory` (Account Directory)
- **Purpose:** Paginated and filterable list of companies.
- **Denormalized data:**
  - ID, Name, Domain
  - `PrimaryContactName`, `PrimaryContactEmail` (extracted from the internal contact entity so as not to load the collection)
  - `OpenDealsCount`, `TotalPipelineValue` (aggregated and cached fields)
  - `LastInteractionDate`
- **Events that update it:** `CompanyRegistered`, `DealCreated/Won/Lost`, `ActivityCompleted`.

## 4. `SalesDashboard` (Commercial Metrics)
- **Purpose:** Show team or user KPIs (Wins, Losses, Win Rate, Average Deal Size).
- **Denormalized data:** Aggregation tables by month/user (lightweight OLAP).
  - `YearMonth`
  - `UserId`
  - `TotalWonAmount`
  - `DealsClosedCount`
- **Events that update it:** `DealWon`, `DealLost`. (Note: If a Won Deal changes its amount retrospectively for correction, there must be a `DealValueCorrected` event that adjusts this table).

## 5. `UpcomingTasks` (Daily Task List)
- **Purpose:** "My day" for a salesperson.
- **Denormalized data:**
  - Pending tasks ordered by date.
  - Included context (`CompanyName` or `LeadName`) so you don't have to click to know who to call.
- **Events that update it:** `ActivityScheduled`, `ActivityCompleted`, `ActivityRescheduled`.

---
**Architecture Note:** In v1 (MVP), these projections can be simple SQL views (Views) or database tables updated synchronously in the same transaction that saves the aggregate. As it scales, they will be moved to specialized databases (Elasticsearch for searches, Redis for counters, or document databases like MongoDB/DynamoDB) updated by an asynchronous event bus (RabbitMQ/Kafka).
