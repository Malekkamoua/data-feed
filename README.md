# Process Data Command

This Laravel Artisan command (`app:process-data`) is designed to process XML data from either a local file or an API endpoint. Depending on the user's choice, it can either save the data directly into a database or generate SQL scripts for manual execution.

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/Malekkamoua/kaufland-data-feed
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

## Usage

### Command Signature

```bash
php artisan app:process-data
   ```
## Command Functionality
### Output Options:

- Saves XML data into a configured database.
- Simply generates database scripts (create - insert).

### Database Configuration (in case the user chose saving data directly into database) :
- Prompts for database credentials (MySQL or SQLite).
- Updates .env file with provided credentials.
- Generates database migration files.

### Data Source:

#### From disk: 
- Reads XML data from a local file.
- Prompts for the complete path to the XML file.
- Validates and processes XML data.
#### From API: 
- Fetches XML data from an API endpoint.
- Prompts for the API URL.
- Fetches XML data using Guzzle HTTP client.
- Validates and processes XML data.

### Generated Files:
* SQL scripts are stored in the storage/app/public/sql_files/ directory.
* Scripts are named based on the XML data structure.

### Requirements
- PHP >= 7.2
- Composer
- Laravel >= 5.8
- Guzzle HTTP client (for API data retrieval)

### Example
Assume you have a local XML file located at storage/data/feed-small.xml and you want to save its data into a MySQL database:

```bash
php artisan app:process-data
```
Follow the prompts to configure the database and specify the data source as disk, providing the path to your XML file.
If you chose the API option, you'll be prompt to provide the adequate link to your XML data.
### Notes
- Ensure proper file permissions for the storage/app/public/sql_files/ directory.
- Handle sensitive database credentials securely, especially in production environments.