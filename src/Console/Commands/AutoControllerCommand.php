<?php

namespace CodingPartners\AutoController\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use CodingPartners\AutoController\Traits\Generates\GenerateRoutes;
use CodingPartners\AutoController\Traits\Generates\GenerateService;
use CodingPartners\AutoController\Traits\Generates\GenerateResource;
use CodingPartners\AutoController\Traits\Generates\GenerateFormRequest;
use CodingPartners\AutoController\Traits\Generates\ControllerWithService;
use CodingPartners\AutoController\Traits\Generates\ControllerWithoutService;

class AutoControllerCommand extends Command
{
    use ControllerWithService ,ControllerWithoutService, GenerateFormRequest, GenerateService, GenerateResource, GenerateRoutes;

    protected $signature = 'crud:generate {model}';
    protected $description = 'Generate CRUD operations for a model';

    /**
     * Handles the main logic of the command.
     *
     * This method is responsible for generating CRUD soperations for a given model.
     * It first checks if the specified table exists in the database. If not, it displays an error message and returns.
     * Then, it retrieves the columns of the table and proceeds to generate the necessary files for CRUD operations.
     *
     * @return void
     */
    public function handle()
    {
        // Retrieve the model name from the command arguments
        $model = $this->argument('model');
        // Convert the model name to a snake_case table name
        $tableName = Str::snake(Str::plural($model));

        // Check if the table exists in the database
        if (!Schema::hasTable($tableName)) {
            $this->error("Table $tableName does not exist.");
            return;
        }

        // Get a list of all the columns in the table
        $columns = Schema::getColumnListing($tableName);

        $this->info("Generating store FormRequest for $model...");
        $this->generateStoreFormRequest($model, $columns);

        $this->info("Generating update FormRequest for $model...");
        $this->generateUpdateFormRequest($model, $columns);

        $this->info("Generating Resource for $model...");
        $this->generateResource($model, $columns);

        // Ask the user if they want to generate a service file
        $generateService = $this->confirm("Do you want to generate a Service for $model?");

        if ($generateService) {
            $this->info("Generating Service for $model...");
            $this->generateService($model, $columns);

            $this->info("Generating CRUD with service for $model...");
            $this->generateControllerWithService($model, $columns);
        } else {
            $this->info("Generating CRUD without service for $model...");
            $this->generateControllerWithoutService($model, $columns);
        }

        $this->info("Generating routes/api.php for $model...");
        $this->generateRoutes($model);
    }
}
