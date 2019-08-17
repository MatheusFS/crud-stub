<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CrudGeneratorCommand extends Command {

    protected $signature = 'crud:generate {name : Class (singular) for example User}';

    protected $description = 'Create CRUD operations';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $name = $this->argument('name');

        $this->controller($name);
        $this->model($name);
        $this->views($name);

        $resourceRouteLine = "Route::resource('".str_plural(strtolower($name))."', '".$name."Controller')->middleware('auth');";
        File::append(base_path('routes/web.php'), $resourceRouteLine);
    }

    protected function getStub($type) {

        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function model($name) {

        $modelTemplate = str_replace(
            ['{{Model}}'],
            [$name],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/{$name}.php"), $modelTemplate);
    }

    protected function controller($name) {

        $controllerTemplate = str_replace(
            [
                '{{Model}}',
                '{{models}}',
                '{{model}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }

    protected function request($name) {

        $requestTemplate = str_replace(
            ['{{Model}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    protected function views($name) {

        $viewIndexTemplate = str_replace(
            [
                '{{models}}',
                '{{model}}'
            ],
            [
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            $this->getStub('ViewIndex')
        );

        $viewShowTemplate = str_replace(
            [
                '{{models}}',
                '{{model}}'
            ],
            [
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            $this->getStub('ViewShow')
        );


        if (!is_dir(resource_path("views/$name")))
            mkdir(resource_path("views/$name"));

        file_put_contents(resource_path("views/$name/index.blade.php"), $viewIndexTemplate);
        file_put_contents(resource_path("views/$name/show.blade.php"), $viewShowTemplate);
    }
}
