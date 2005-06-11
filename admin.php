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

$login = (string) @$_POST['login'];
$password = (string) @$_POST['password'];

// On fait les traitements ici pour pouvoir bénéficier de la redirection
if (isset($_POST['submit']) && $login !== '' && $password !== '') {
    $conn = connect(FALSE);
    if ($conn) {
	$req = @pg_query($conn, 'SELECT niveau FROM Utilisateur ' .
				'WHERE nom=\'' . $login . '\' AND pwd = \'' .
				md5($password) . '\'');
	if ($req) {
	    $line = @pg_fetch_array($req);
	    if ($line) {
		$level = (integer) $line['niveau'];

		if (isset($_GET['redir'])) {
		    $url = 'http://' . $_SERVER['HTTP_HOST'];
		    if (isset($_SERVER['HTTP_PORT']) &&
			$_SERVER['HTTP_PORT'] !== '80')
			$url .= ':' . $_SERVER['HTTP_PORT'];
		    $url .= dirname($_SERVER['PHP_SELF']) . '/' .
		    $_GET['redir'] . '.php';
		    $do_redir = $url;
		}

		$line = TRUE;
	    } else
		$line = FALSE;

	    $req = TRUE;
	} else {
            $req = FALSE;
	    $line = FALSE;
	}

	disconnect();
	$conn = TRUE;
    } else {
	$conn = FALSE;
	$req = FALSE;
	$line = FALSE;
    }
}

require('top.inc.php');

?>
	<h2>Accès administrateur</h2>

<?php

$urladd3 = isset($_GET['redir']) ?
	   $urladd2 . 'redir=' . $_GET['redir'] : $urladd;

if (isset($_POST['submit']) && $login !== '' && $password !== '') {
    if (!$conn || !$req)
	db_error();

    if ($line)
	echo "\t<p>Authentification réussie.</p>\n";
    else {
	echo "\t<p>Authentification échouée.</p>\n";
	echo "\t<p><a href=\"$self$urladd3\">Réessayer</a></p>\n";
    }
} else {
    if (isset($_POST['submit'])) {
	echo "\t<p>\n";
	if ($login === '')
	    echo "\t  <strong class=\"error\">L'identifiant est vide.</strong><br />\n";
	if ($password === '')
	    echo "\t  <strong class=\"error\">Le mot de passe est vide.</strong><br />\n";
	echo "\t</p>\n";
    }
?>
	<form action="<?php echo $self . $urladd3; ?>" method="post">
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
	    </tbody>
	  </table>
	  <p><input type="submit" name="submit" value="S'authentifier" /></p>
	</form>

<?php
}

require('bottom.inc.php');

?>
