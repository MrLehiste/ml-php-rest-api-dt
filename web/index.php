<?php
require('../vendor/autoload.php');
$app = new Silex\Application();
$app['debug'] = true;
// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));
// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
// Our web handlers
$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
    array(
        'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/').';host='.$dbopts["host"] . ';port=' . $dbopts["port"],
        'pdo.username' => $dbopts["user"],
        'pdo.password' => $dbopts["pass"]
    )
);

$app->get('/db/', function() use($app) {
  $app['monolog']->addDebug( getenv('DATABASE_URL') );
  return json_encode(parse_url(getenv('DATABASE_URL')));
});

$app->run();