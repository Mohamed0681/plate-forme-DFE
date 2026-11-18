<?php require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/functions.php'; if(is_logged_in()){header('Location: dashboard.php');exit;} $error=''; if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$st=$pdo->prepare("SELECT * FROM users WHERE email=?");$st->execute([trim($_POST['email']??'')]);$u=$st->fetch();if($u&&password_verify($_POST['password']??'',$u['password'])){session_regenerate_id(true);$_SESSION['user_id']=$u['id'];$_SESSION['user_name']=$u['name'];$_SESSION['role']=$u['role'];header('Location: dashboard.php');exit;} $error='Identifiants incorrects.';} ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Connexion | Administration</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?=e(url('assets/css/admin.css'))?>"></head>
<body class="admin-login"><form class="login-card" method="post">
<a class="adm-logo" href="<?=e(url('index.php'))?>"><img src="<?=e(url('assets/img/logo.png'))?>" alt="Logo" width="88" height="88"></a>
<h1>Espace administrateur</h1><p class="sub">Connectez-vous pour gérer le contenu du site.</p>
<?php if($error): ?><p class="alert err"><?=e($error)?></p><?php endif; ?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>E-mail<input type="email" name="email" required autofocus></label><label>Mot de passe<input type="password" name="password" required></label>
<button class="btn-g">Se connecter</button><a class="back" href="<?=e(url('index.php'))?>">← Retour au site</a></form></body></html>
