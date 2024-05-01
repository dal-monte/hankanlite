<?php

require __DIR__ . '/../../vendor/autoload.php';

class Application
{
    protected $request;
    protected $router;
    protected $response;
    protected $databaseManager;
    protected $library;
    protected $validate;
    protected $convert;
    protected $token;
    protected $reCaptcha;

    public function __construct()
    {
        $this->library = new Library();
        $this->router = new Router($this->registerRoutes());
        $this->request = new Request();
        $this->response = new Response();
        $this->databaseManager = new DatabaseManager();
        $this->validate = new Validate();
        $this->convert = new Convert();
        $this->token = new Token();
        $this->reCaptcha = new reCaptcha();
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $this->databaseManager->connect(
            [
                'hostname' => $_ENV['DB_HOST'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'database' => $_ENV['DB_DATABASE'],
            ]
        );
        $this->reCaptcha->connect(
            [
                'siteKey' => $_ENV['reCaptcha_siteKey'],
                'secretKey' => $_ENV['reCaptcha_secretKey'],
            ]
        );
    }

    public function run()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if (!$params) {
                throw new HttpNotFoundException();
            }

            $controller = $params['controller'];
            $action = $params['action'];
            $this->runAction($controller, $action);
        } catch (HttpNotFoundException) {
            $this->render404Page();
        }

        $this->response->send();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getDatabaseManager()
    {
        return $this->databaseManager;
    }

    public function getReCaptcha()
    {
        return $this->reCaptcha;
    }

    public function getLibrary()
    {
        return $this->library;
    }

    public function getValidate()
    {
        return $this->validate;
    }

    public function getConvert()
    {
        return $this->convert;
    }

    public function getToken()
    {
        return $this->token;
    }

    private function runAction($controllerName, $action)
    {
        $controllerClass = ucfirst($controllerName) . 'Controller';
        if (!class_exists($controllerClass)) {
            throw new HttpNotFoundException();
        }
        $controller = new $controllerClass($this);
        $content = $controller->run($action);
        $this->response->setContent($content);
    }

    private function registerRoutes()
    {
        return $this->library->routes;
    }

    private function render404Page()
    {
        $this->response->setStatusCode(404, 'Not Found');
        $this->response->setContent(
            <<<EOF
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>
</head>
<body>
    <h1>
        404 Page Not Found.
    </h1>
    <h2>
        不正な操作を検知しました。
    </h2>
    <a href="/login"><h2>ログイン画面へ戻る</h2></a>
</body>
</html>
EOF
        );
    }
}
