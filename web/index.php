<?php
use 
	Symfony\Component\HttpFoundation\Request,
	Symfony\Component\HttpFoundation\Response;
require('../vendor/autoload.php');
$app = new Silex\Application();
$app['debug'] = true;
// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array('monolog.logfile' => 'php://stderr', ));
// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views', ));

// Loading environment variables (for dev env)
$dotenv = new Dotenv\Dotenv(__DIR__);
try{ $dotenv->load(); $app['monolog']->addDebug('$dotenv->load(); finished'); }
catch(Exception $e) { $app['monolog']->addDebug('$dotenv->load(); FAILED'); }

// Our web handlers
$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$dbopts = parse_url(getenv('DATABASE_URL'));
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

//$app->get('/db/', function() use($app) { $app['monolog']->addDebug( getenv('DATABASE_URL') ); return json_encode(parse_url(getenv('DATABASE_URL'))); });

$app->get('/users/', function (Request $request) use ($app) {
    //$names = $app['db']->fetchAll('SELECT first_name as name FROM users');
    //return $app['twig']->render('database.twig', array('names' => $names));
    $users = $app['db']->fetchAll('SELECT * FROM users');
    return $app->json($users, 200);
});
$app->get('/users/{id}', function (Request $request, $id) use ($app) {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));
    return $app->json($post, 200);
});
//create new user
$app->post('/users/', function (Request $request) use ($app) {
    $user = array(
    	'first_name' => $request->get('first_name'),
    	'last_name' => $request->get('last_name'),
        'email' => $request->get('email'),
        'password' => $request->get('password'),
    );
    $app['db']->insert('users', $user);
    return new Response("User " . $app['db']->lastInsertId() . " created", 201);
});
//update user
$app->post('/users/{id}', function (Request $request, $id) use ($app) {
    // $sql = "UPDATE users SET email = ?, name = ? WHERE user_id = ?";
    // $app['db']->executeUpdate($sql, array(
    // 	$request->get('email'),
    // 	$request->get('name'),
    // 	(int) $id)
    // );
    return $app->json($request->request->all(), 200);
});
//delete user
$app->delete('/users/{id}', function (Request $request, $id) use ($app) {
    $sql = "DELETE FROM users WHERE user_id = ?";
    $app['db']->executeUpdate($sql, array((int) $id));
    return new Response("User " . $id . " deleted", 204);
});

$app->run();
