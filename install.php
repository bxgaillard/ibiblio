<?php

/*
 * ---------------------------------------------------------------------------
 *
 * iBiblio : base de données bibliographiques
 * Copyright (C) 2005 Benjamin Gaillard & Nicolas Riegel
 *
 * ---------------------------------------------------------------------------
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier conformément aux dispositions de la Licence Publique Générale GNU,
 * telle que publiée par la Free Software Foundation ; version 2 de la
 * licence, ou encore (à votre convenance) toute version ultérieure.
 *
 * Ce programme est distribué dans l'espoir qu'il sera utile, mais SANS AUCUNE
 * GARANTIE ; sans même la garantie implicite de COMMERCIALISATION ou
 * D'ADAPTATION À UN OBJET PARTICULIER. Pour plus de détail, voir la Licence
 * Publique Générale GNU.
 *
 * Vous devez avoir reçu un exemplaire de la Licence Publique Générale GNU en
 * même temps que ce programme ; si ce n'est pas le cas, écrivez à la Free
 * Software Foundation Inc., 675 Mass Ave, Cambridge, MA 02139, États-Unis.
 *
 * ---------------------------------------------------------------------------
 */


require_once('common.inc.php');
if (!connect(FALSE))
	unset($db_configured);
disconnect();

// On crée une session
if (!isset($db_configured) && isset($_POST['submit']))
	$level = 2;
require('top.inc.php');

?>

<!-- Formulaire pour l'installation du site et de la base de donnée -->

	<h2><?php echo isset($db_configured) ? 'Réinstallation' : 'Installation'; ?></h2>

<?php

// Vérification des droits
if (isset($db_configured))
	require_rights(2);

// On récupère les données du formulaire
$host = (string) @$_POST['host'];
$login = (string) @$_POST['login'];
$pwd = (string) @$_POST['pwd'];
$pwd2 = (string) @$_POST['pwd2'];
$bddname = (string) @$_POST['bddname'];
$adminLogin = (string) @$_POST['adminLogin'];
$adminPwd = (string) @$_POST['adminPwd'];
$adminPwdVerif = (string) @$_POST['adminPwdVerif'];

// On vérifie si les champs sont remplis
if( $login != '' && $pwd != '' && $pwd2 != '' && $bddname != '' && $pwd == $pwd2 &&
    $adminLogin != '' && $adminPwd != '' && $adminPwdVerif != '' && $adminPwd == $adminPwdVerif )
{
	// Installation

	$error=0;

	// On va créer le fichier de connexion à la base de donnée
	
	// On met le nom du futur fichier dans une variable
	$filename = 'connection.conf.php';
	
	// On met le contenu du futur fichier dans une variable
	$contenu = <<<EOF
<?php

\$db_configured = TRUE;
\$db_host = '$host';
\$db_name = '$bddname';
\$db_user = '$login';
\$db_password = '$pwd';

?>
EOF;

	// On ouvre le fichier en écriture, on le tronque s'il existe et on le crée s'il n'existe pas
	if(!$handle = @fopen($filename, "w"))
		fatal_error("Impossible d'ouvrir le fichier \"$filename\".");

	// On écrit le contenu dans le fichier.
	if (@fwrite($handle, $contenu) === FALSE)
		fatal_error('Impossible d\'écrire dans le fichier "' . $filename . '".<br />' .
			    'Vérifiez que vous avez bien donné les droits d\'écriture à ce fichier.');
 
	// On ferme le fichier
	@fclose($handle);

	require('tables.php');
	drop_tables();
	$res = create_tables();
	
	$conn = connect();
	$req = @pg_query($conn, 'INSERT INTO Utilisateur (nom, pwd, niveau) VALUES (\'' . $adminLogin . '\', \'' . md5($adminPwd) . '\', 2)');
	if (!$req)
		db_error();
	disconnect();
	
	switch($res)
	{
		case 0 : break;
		case 1 : fatal_error("Erreur lors de la création des tables. Peut-être existent-elles déjà ?<br />"
			. "<a href=\"$self$urladd\">Réessayer</a>");
		case 2 : fatal_error("Erreur lors de la connexion à la base de données. Avez-vous bien installé le site?<br />"
			. "<a href=\"$self$urladd\">Réessayer</a>");
		default : fatal_error("Une erreur inconnue s'est produite.<br />"
			. "<a href=\"$self$urladd\">Réessayer</a>");
	}

	$level = 2;
	$_SESSION['level'] = $level;
	echo "\t<p>L'installation du site s'est bien déroulée.</p>\n";
}

// S'ils ne sont pas tous remplis, on vérifie lesquels sont remplis et on demande de compléter les autres
// Si aucun n'est rempli, on affiche simplement le formulaire d'installation
else
{
	if(isset($_POST['submit']) && ($login == '' || $pwd == '' || $pwd2 == '' || $bddname == '' ||
	    $adminLogin == '' || $adminPwd == '' || $adminPwdVerif == '' ))
	{
		echo "\t<p>";
		if($login == '')
			echo "\t  Le champ \"Login\" n'est pas rempli.<br />\n";
		if($pwd == '')
			echo "\t  Le champ \"Mot de passe\" n'est pas rempli.<br />\n";
		if($pwd2 == '')
			echo "\t  Le champ \"Vérification du mot de passe\" n'est pas rempli.<br />\n";
		if($bddname == '')
			echo "\t  Le champ \"Nom de la base de données\" nest pas rempli.<br />\n";
		if($pwd != $pwd2)
			echo "\t  Le mot de passe est mal vérifié.<br />\n";
		if($adminLogin == '')
			echo "\t  Le champ \"Login de l'administrateur\" n'est pas rempli.<br />\n";
		if($adminPwd == '')
			echo "\t  Le champ \"Mot de passe de l'administrateur\" n'est pas rempli.<br />\n";
		if($adminPwdVerif == '')
			echo "\t  Le champ \"Vérification du mot de passe de l'administrateur\" n'est pas rempli.<br />\n";
		if($adminPwd != $adminPwdVerif)
			echo "\t  Le mot de passe de l'administrateur est mal vérifié.<br />\n";
		echo "\t</p>\n";
	}
?>

<!-- Formulaire pour l'installation du site et de la base de donnée -->

	<p>
	  Attention cette page va installer (ou réinstaller) tout le site.<br />
	  Elle va donc supprimer l'ancienne base de donnée.
	</p>

	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <p>Informations de connexion à la base de données&nbsp;:</p>
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Serveur PostgreSQL&nbsp;:</th>
		<td><input type="text" name="host" value="<?php echo $host; ?>" /> <em>Laisser vide pour une connexion locale</em></td>
	      </tr>
	      <tr>
		<th>Identifiant&nbsp;:</th>
		<td><input type="text" name="login" value="<?php echo $login; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Mot de passe&nbsp;:</th>
		<td><input type="password" name="pwd" value="<?php echo $pwd2; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Vérification du mot de passe&nbsp;:</th>
		<td><input type="password" name="pwd2" value="<?php echo $pwd; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Nom de la base de données&nbsp;:</th>
		<td><input type="text" name="bddname" value="<?php echo $bddname; ?>" /> <em>obligatoire</em></td>
	      </tr>
	    </tbody>
	  </table>

	  <p>
	    Compte administrateur du site&nbsp;:<br />
	    C'est le premier compte créé, nécessaire pour en créer d'autres.
	  </p>
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Identifiant&nbsp;:</th>
		<td><input type="text" name="adminLogin" value="<?php echo $adminLogin; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Mot de passe&nbsp;:</th>
		<td><input type="password" name="adminPwd" value="<?php echo $adminPwd; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Mot de passe (vérification)&nbsp;:</th>
		<td><input type="password" name="adminPwdVerif" value="<?php echo $adminPwdVerif; ?>" /> <em>obligatoire</em></td>
	      </tr>
	    </tbody>
	  </table>

	  <p><input type="submit" name="submit" value="Installer" /></p>
	</form>

	<h2>Désinstallation</h2>
	<p><a href="uninstall.php<?php echo $urladd; ?>">Cliquez ici</a> pour désinstaller le site.</p>
<?php
}

require('bottom.inc.php');

?>
