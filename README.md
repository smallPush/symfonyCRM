# Symfony CRM

A modern, lightweight CRM built with Symfony 7.4 for managing fundraising campaigns, donors, transactions, and assets.

## Features

- **Campaign Management**: Create and track fundraising campaigns with financial goals and video configurations.
- **Donor Tracking**: Manage donor information including contact details and transaction history.
- **Asset Management**: Associate digital assets (images, documents) with specific campaigns.
- **Financial Insights**: Track current amounts against goals, investment totals, and ROI (Return on Investment) for each campaign.
- **Manager Assignment**: Assign users to manage specific campaigns.

## Tech Stack

- **Backend**: PHP 8.2+ & Symfony 7.4
- **Database**: PostgreSQL 16
- **Frontend**: Twig, Asset Mapper, Stimulus, and Turbo (no Node.js required!)
- **Infrastructure**: Docker & Docker Compose

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose installed.
- [Composer](https://getcomposer.org/) (optional, if running outside Docker).

### Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd symfonyCRM
   ```

2. **Start the environment**:
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies**:
   ```bash
   docker-compose exec app composer install
   ```

4. **Run migrations**:
   ```bash
   docker-compose exec app php bin/console doctrine:migrations:migrate
   ```

5. **Load sample data (optional)**:
   ```bash
   docker-compose exec app php bin/console doctrine:fixtures:load
   ```

### Accessing the Application

The application will be available at [http://localhost:8081](http://localhost:8081).

## Development

### Project Structure

- `src/Entity/`: Domain models (Campaign, Donor, Transaction, Asset, User).
- `src/Controller/`: Web controllers.
- `src/Form/`: Symfony forms for data handling.
- `assets/`: Frontend assets managed by Asset Mapper.
- `templates/`: Twig templates for the UI.

### Running Tests

To run the test suite:
```bash
docker-compose exec app php bin/phpunit
```

## License

This project is proprietary. See `composer.json` for more details.
