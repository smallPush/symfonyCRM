# 2. Internal Entities per Aggregate

In DDD, internal entities live within the boundaries of an Aggregate Root. They have their own identity within that context, but **they cannot be accessed or modified directly from outside the aggregate**. Any modification must go through the Aggregate Root to ensure business rules and invariants.

Below are the internal entities for each of the defined main Aggregate Roots.

## Within the `Company` Aggregate

### `Contact`
- **What it represents:** A person who works for the company or who is the point of contact within it.
- **Identity:** It has its own ID (e.g., UUID) so it can be referenced internally or from a Deal/Activity, but its life cycle depends on `Company`.
- **Why it is an internal entity:** A contact does not exist in a vacuum in this B2B CRM. If the company is deleted, its contacts are generally archived with it. Modifying a contact (e.g., changing its role to "Decision Maker") must be validated at the `Company` level to ensure that there is, for example, at least one primary point of contact.

### `Address` (Physical Address / Headquarters)
- **What it represents:** A physical location of the company (Headquarters, Regional Office, Warehouse).
- **Identity:** It can have its own ID if the company handles multiple locations and needs to differentiate them for billing or shipping.
- **Why it is an internal entity:** Its existence is meaningless without the company to which it belongs.

## Within the `Deal` Aggregate

### `DealNote`
- **What it represents:** A quick comment or specific note about the ongoing opportunity, less formal than a scheduled `Activity`.
- **Identity:** Own ID so it can be edited/deleted.
- **Why it is an internal entity:** It is part of the internal state of the Deal. It makes no sense to search for "all orphan notes"; they are always read together with the Deal.

### `DealParticipant`
- **What it represents:** A `Contact` (referenced by its ID) that has a specific role in this Deal (e.g., "Influencer", "Decision Maker", "Legal Counsel").
- **Identity:** Own ID of the relationship or local identity within the Deal.
- **Why it is an internal entity:** The role of a person in a specific business is a detail of that Deal. It is modified through `Deal->AddParticipant(contactId, role)`.

## Within the `Activity` Aggregate

### `ActivityAttachment`
- **What it represents:** A file or document (e.g., a proposal PDF) attached to an email or to meeting notes.
- **Identity:** Internal ID and reference to the stored file.
- **Why it is an internal entity:** If the activity is deleted, the attachments also lose their main context in this view.

## What are NOT internal entities?

It is a common mistake to model `Activity` as an internal entity within `Deal` or `Company`.
**Why is it a mistake?**
- **Concurrency:** If a user adds a note to the `Company` while another updates the business address, it would cause a block in the `Company` aggregate.
- **Pagination/Volume:** A `Company` can have thousands of activities over the years. Loading the `Company` aggregate in memory with all its activities (or managing giant collections) is inefficient.
- Therefore, `Activity` is its own Aggregate Root and simply makes a reference (saves the ID) to the `Company` or the `Deal`.
