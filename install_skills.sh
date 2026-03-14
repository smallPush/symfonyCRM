#!/bin/bash
mkdir -p ~/.gemini/antigravity/skills/entity-campaign
mkdir -p ~/.gemini/antigravity/skills/entity-donor
mkdir -p ~/.gemini/antigravity/skills/entity-transaction
mkdir -p ~/.gemini/antigravity/skills/entity-asset
mkdir -p ~/.gemini/antigravity/skills/entity-user

cat << 'INNER_EOF' > ~/.gemini/antigravity/skills/entity-campaign/SKILL.md
---
name: entity-campaign
description: "Understand and use the Campaign entity which represents a fundraising initiative with financial goals, ROI, managers, assets, and transactions."
---

# Campaign Entity

## Overview
The `Campaign` entity is the core model representing a fundraising initiative. It tracks financial goals, current funding progress, return on investment (ROI), and associations with managers (Users), digital files (Assets), and financial contributions (Transactions).

## When to Use This Skill
- Use when creating or modifying controllers, forms, or services that deal with Campaigns.
- Use when querying the `CampaignRepository`.
- Use when relating other entities (Donors, Transactions, Assets, Users) to a Campaign.

## How It Works

### Properties
- `id` (int): Primary key.
- `title` (string, max 255): The title of the campaign.
- `description` (text, nullable): Detailed description of the campaign.
- `videoConfig` (array, nullable): Configuration settings for associated campaign videos.
- `financialGoal` (decimal 12,2): The target fundraising amount.
- `currentAmount` (decimal 12,2): The currently raised amount (defaults to '0.00').
- `totalInvestment` (decimal 12,2, nullable): The total investment made in the campaign.
- `roiPercentage` (decimal 5,2, nullable): Return on investment percentage.
- `createdAt` (DateTimeImmutable): Timestamp of creation.

### Relationships
- `assets` (OneToMany -> Asset): A collection of digital assets associated with this campaign. Orphan removal is enabled (if an asset is removed from the campaign, it is deleted).
- `transactions` (OneToMany -> Transaction): A collection of financial contributions towards this campaign.
- `managers` (ManyToMany <- User): Users who are assigned to manage this campaign. This is the inverse side of the relationship (`User::$managedCampaigns` is the owning side). It uses `fetch: 'EXTRA_LAZY'`.

## Best Practices
- ✅ Always use the provided adder/remover methods (e.g., `addAsset()`, `removeAsset()`, `addManager()`) to maintain bidirectional integrity.
- ✅ When validating user input for a Campaign, ensure `financialGoal` is positive and `title` is not empty using Symfony Validator constraints.
- ✅ When querying collections like `managers` or `transactions`, consider using explicit `select()` queries in the repository if you don't need the full entity hydration to save memory.
- ❌ Do not manually instantiate the `$assets`, `$transactions`, or `$managers` properties. They are initialized as `ArrayCollection` in the constructor.

## Common Pitfalls
- **Problem:** Adding a manager to a campaign doesn't save to the database.
  **Solution:** Because `managers` is the inverse side of the ManyToMany relationship with `User`, ensure the changes are cascaded or persisted from the `User` entity, or ensure the adder method `addManager` calls `$manager->addManagedCampaign($this)`.
- **Problem:** Large memory usage when checking if a user manages a campaign.
  **Solution:** The relationship uses `fetch: 'EXTRA_LAZY'`, so `$campaign->getManagers()->contains($user)` will run an optimized SQL query instead of loading all managers into memory. Always rely on `contains()`.
INNER_EOF

cat << 'INNER_EOF' > ~/.gemini/antigravity/skills/entity-donor/SKILL.md
---
name: entity-donor
description: "Understand and use the Donor entity which represents an individual making financial contributions, including contact info and their transactions."
---

# Donor Entity

## Overview
The `Donor` entity represents an individual who makes financial contributions to campaigns. It stores their personal and contact information and links them to all their transaction history.

## When to Use This Skill
- Use when creating or updating donor profiles.
- Use when linking new financial contributions (`Transaction`) to a user.
- Use when querying the `DonorRepository` for donor-specific metrics or history.

## How It Works

### Properties
- `id` (int): Primary key.
- `firstName` (string, max 255): The donor's first name.
- `lastName` (string, max 255): The donor's last name.
- `email` (string, max 255): The donor's email address (unique constraint).
- `phone` (string, max 20, nullable): The donor's phone number.
- `createdAt` (DateTimeImmutable): Timestamp of when the donor record was created.

### Relationships
- `transactions` (OneToMany -> Transaction): A collection of all financial transactions made by this donor.

## Best Practices
- ✅ Ensure emails are validated using Symfony Validator constraints (`#[Assert\Email]`) before persisting.
- ✅ Use `addTransaction()` and `removeTransaction()` to manage the donor's contributions. This automatically handles the bidirectional relationship.
- ❌ Do not create duplicate donor records. Use the `email` field to check for existing donors.

## Common Pitfalls
- **Problem:** Constraint violation on email when creating a new donor.
  **Solution:** The `email` column is unique. Always verify if a donor with the given email already exists using the `DonorRepository` before creating a new one.
INNER_EOF

cat << 'INNER_EOF' > ~/.gemini/antigravity/skills/entity-transaction/SKILL.md
---
name: entity-transaction
description: "Understand and use the Transaction entity which represents a financial contribution from a Donor to a Campaign."
---

# Transaction Entity

## Overview
The `Transaction` entity represents a specific financial contribution. It links a `Donor` to a `Campaign` and records the monetary value, currency, status, and payment gateway reference (Stripe).

## When to Use This Skill
- Use when processing payments, webhooks, or financial reports.
- Use when working with Stripe integration to update transaction statuses.
- Use when querying the `TransactionRepository` to calculate campaign totals or donor histories.

## How It Works

### Properties
- `id` (int): Primary key.
- `amount` (decimal 10,2): The financial amount of the transaction.
- `currency` (string, max 3): The currency code, defaults to 'USD'.
- `status` (string, max 50): The current status of the transaction (e.g., 'pending', 'succeeded', 'failed').
- `stripePaymentIntentId` (string, max 255): The unique reference ID from the Stripe payment gateway (unique constraint).
- `createdAt` (DateTimeImmutable): Timestamp of the transaction.

### Relationships
- `donor` (ManyToOne -> Donor): The donor who made the transaction (nullable: false).
- `campaign` (ManyToOne -> Campaign): The campaign the transaction is supporting (nullable: false).

## Best Practices
- ✅ Always set both the `donor` and `campaign` when creating a transaction, as they are non-nullable.
- ✅ Handle the `status` field carefully, updating it based on actual payment gateway webhooks.
- ❌ Do not change the `amount` or `stripePaymentIntentId` after a transaction has been successfully processed and verified.

## Common Pitfalls
- **Problem:** Updating campaign totals is out of sync with transactions.
  **Solution:** Ensure that when a transaction status changes to 'succeeded', you also update the corresponding `Campaign::$currentAmount`. Consider using Doctrine event listeners or a dedicated service for this to maintain data consistency.
INNER_EOF

cat << 'INNER_EOF' > ~/.gemini/antigravity/skills/entity-asset/SKILL.md
---
name: entity-asset
description: "Understand and use the Asset entity representing digital files (images, documents) associated with a Campaign."
---

# Asset Entity

## Overview
The `Asset` entity represents a digital file, such as an image, document, or video, associated with a specific campaign. It tracks the original filename, mime type, and storage path.

## When to Use This Skill
- Use when handling file uploads for campaigns (e.g., promotional images or attachments).
- Use when generating download links or displaying assets in Twig templates.
- Use when querying the `AssetRepository` to clean up orphaned files.

## How It Works

### Properties
- `id` (int): Primary key.
- `filename` (string, max 255): The original name of the file.
- `mimeType` (string, max 100): The MIME type of the file (e.g., 'image/jpeg').
- `filePath` (string, max 255): The path where the file is physically stored on the server or storage service.

### Relationships
- `campaign` (ManyToOne -> Campaign): The campaign this asset belongs to (nullable: false).

## Best Practices
- ✅ Always link the asset to its `Campaign` using the `setCampaign()` method.
- ✅ Handle physical file deletion when an asset entity is removed (e.g., using a Doctrine preRemove/postRemove lifecycle callback).
- ❌ Do not store file contents in the database; always store paths or URLs in `filePath`.

## Common Pitfalls
- **Problem:** Database assets exist, but files are missing from storage.
  **Solution:** Ensure file operations and database persistence are synchronized, possibly using transactions.
INNER_EOF

cat << 'INNER_EOF' > ~/.gemini/antigravity/skills/entity-user/SKILL.md
---
name: entity-user
description: "Understand and use the User entity representing system users and their managed campaigns, acting as the authentication provider."
---

# User Entity

## Overview
The `User` entity is the authentication provider for the application. It represents administrators, organizers, and editors, mapping their roles and the campaigns they manage. It implements `UserInterface` and `PasswordAuthenticatedUserInterface`.

## When to Use This Skill
- Use when implementing authentication, authorization (Voters, `#[IsGranted]`), or JWT configurations.
- Use when assigning campaign managers or listing managed campaigns for a specific user.
- Use when querying the `UserRepository` to find specific roles or manage user accounts.

## How It Works

### Properties
- `id` (int): Primary key.
- `email` (string, max 180): The user's email, used as the unique identifier for authentication (unique constraint).
- `roles` (array of strings): The user's roles (e.g., `ROLE_USER`, `ROLE_ADMIN`, `ROLE_ORGANIZATION`, `ROLE_EDITOR`).
- `password` (string): The hashed password.

### Relationships
- `managedCampaigns` (ManyToMany -> Campaign): The campaigns this user is assigned to manage. This is the owning side of the relationship (`Campaign::$managers` is the inverse side). It uses `fetch: 'EXTRA_LAZY'`.

## Best Practices
- ✅ Always use the defined role constants (e.g., `User::ROLE_ADMIN`, `User::ROLE_ORGANIZATION`) instead of magic strings.
- ✅ Ensure passwords are hashed before persisting the entity.
- ✅ When verifying if a user manages a campaign, use the `fetch: 'EXTRA_LAZY'` feature: `$user->getManagedCampaigns()->contains($campaign)` for performance.
- ❌ Do not manually implement the `__serialize` method to leak the password hash; it's correctly handled with a `crc32c` hash for session security in Symfony 7.3+.

## Common Pitfalls
- **Problem:** Adding a campaign to a user's managed campaigns doesn't persist.
  **Solution:** As the owning side of the relationship, changes to `managedCampaigns` will be persisted when the `User` is saved. The `addManagedCampaign()` method does not automatically call `$campaign->addManager($this)`, so be aware of keeping the two sides synchronized in memory if needed during a single request lifecycle.
INNER_EOF
