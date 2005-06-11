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
	<h2>Supprimer un utilisateur</h2>

<?php

$conn = connect();

if (isset($_POST['user'])) {
    $user = (integer) $_POST['user'];

    $req = @pg_query('SELECT COUNT(*) FROM Utilisateur WHERE niveau >= 2 ' .
		     'AND idUtil != ' . $user);
    if (!$req)
	db_error();
    $count = (integer) @pg_fetch_result($req, 0);

    if ($count > 0) {
	$req = @pg_query('DELETE FROM Utilisateur WHERE idUtil = ' . $user);
	if (!$req)
	    db_error();
	echo "\t<p>Utilisateur correctement supprimé.</p>\n";
    } else
	echo "\t<p>Ne peut pas supprimer cet utilisateur&nbsp;: il ne " .
	     "reste plus qu'un administrateur.</p>\n";
} else {
    $req = @pg_query('SELECT COUNT(*) FROM Utilisateur');
    if (!$req)
	db_error();
    $count = (integer) @pg_fetch_result($req, 0);

    if ($count > 1) {
?>

	<form action="<?php echo $self . $urladd; ?>" method="post">
	  <p>Sélectionnez un utilisateur à supprimer&nbsp;:</p>
	  <p>
	    <select name="user">
<?php
	$req = @pg_query('SELECT idUtil, nom, niveau FROM Utilisateur ' .
			 'ORDER BY nom');
	if (!$req)
	   db_error();
	while ($line = @pg_fetch_array($req)) {
	    echo "\t" . '      <option value="' . $line['idutil'] . '">' .
		 $line['nom'] .
		 ($line['niveau'] >= 2 ? ' (administrateur)' : '') .
		 '</option>' . "\n";
	}
?>
	    </select>
	    <input type="submit" name="submit" value="Supprimer" />
	  </p>
	</form>

<?php

    } else
	echo "\t<p>Il n'y a qu'un seul utilisateur.</p>\n";
}

disconnect();

require('bottom.inc.php');

?>
