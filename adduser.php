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


require('top.inc.php');

?>
	<h2>Ajout d'un utilisateur</h2>

<?php

require_rights(2);

$login = (string) @$_POST['login'];
$password = (string) @$_POST['password'];
$passwordVerif = (string) @$_POST['passwordVerif'];
$admin = (string) @$_POST['admin'];

if (isset($_POST['submit']) && $login !== '' && $password !== '' &&
    $passwordVerif !== '' && $password === $passwordVerif) {
    $conn = connect();

    $req = @pg_query('SELECT nom FROM Utilisateur WHERE nom = \'' .
		     $login . '\'');
    if (!$req)
	db_error();

    if (@pg_fetch_array($req)) {
	echo "\t<p>L'utilisateur \"$login\" existe déjà.</p>\n";
    } else {
	$userlevel = $admin === 'yes' ? 2 : 1;
	$req = @pg_query('INSERT INTO Utilisateur (nom, pwd, niveau) ' .
			 'VALUES (\'' . $login . '\', \'' . md5($password) .
			 '\', ' . $userlevel . ')');
	if (!$req)
	    db_error();

	echo "\t<p>L'utilisateur \"$login\" a été ajouté avec succès.</p>\n";
    }
    disconnect();
} else {
    if (isset($_POST['submit'])) {
	echo "\t<p>\n";
	if ($login === '')
	    echo "\t  <strong class=\"error\">L'identifiant est vide.</strong><br />\n";
	if ($password === '')
	    echo "\t  <strong class=\"error\">Le mot de passe est vide.</strong><br />\n";
	if ($passwordVerif === '')
	    echo "\t  <strong class=\"error\">La vérification de mot de passe est vide.</strong><br />\n";
	if ($password !== $passwordVerif)
	    echo "\t  <strong class=\"error\">Le mot de passe et sa vérification ne correspondent pas.</strong><br />\n";
	echo "\t</p>\n";
    }

?>

	<form action="<?php echo $self . $urladd; ?>" method="post">
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Identifiant&nbsp;:</th>
		<td><input type="text" name="login" value="<?php echo $login; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Mot de passe&nbsp;:</th>
		<td><input type="password" name="password" value="<?php echo $password; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Mot de passe (vérification)&nbsp;:</th>
		<td><input type="password" name="passwordVerif" value="<?php echo $passwordVerif; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Compte administrateur&nbsp;</th>
		<td><input type="checkbox" name="admin" value="yes"<?php if ($admin === 'yes') echo ' checked="checked"'; ?> /> <em>(peut créer d'autres utilisateurs et installer/désinstaller le site)</em></td>
	      </tr>
	    </tbody>
	  </table>

	  <p><input type="submit" name="submit" value="Ajouter" /></p>
	</form>

<?php

}

require('bottom.inc.php');

?>
