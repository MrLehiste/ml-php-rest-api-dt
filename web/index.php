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

// Loading environment variables (for dev env)
$dotenv = new Dotenv\Dotenv(__DIR__);
try{
  $dotenv->load();
  $app['monolog']->addDebug('$dotenv->load(); finished');
}
catch(Exception $e) {
  $app['monolog']->addDebug('$dotenv->load(); FAILED');
}

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
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_pgsql',
        'dbname'   => ltrim($dbopts["path"],'/'),
        'host'     => $dbopts["host"],
        'user'     => $dbopts["user"],
        'password' => $dbopts["pass"],
        'port'     => $dbopts["port"],
    ),
));

$app->get('/db/', function() use($app) {
  $app['monolog']->addDebug( getenv('DATABASE_URL') );
  return json_encode(parse_url(getenv('DATABASE_URL')));
});

$app->get('/users', function () use ($app) {
    $names = array();
    $names = $app['db']->fetchAll('SELECT first_name as name FROM users');

    return $app['twig']->render('database.twig', array('names' => $names));
});
$app->get('/x/users/', function() use($app) {
  $st = $app['pdo']->prepare('SELECT first_name as name FROM users');
  $st->execute();

  $names = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $app['monolog']->addDebug('Row ' . $row['first_name']);
    $names[] = $row;
  }

  return $app['twig']->render('database.twig', array(
    'names' => $names
  ));
});

$app->run();
