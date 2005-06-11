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

if (isset($_POST['confirmer'])) {
    session_start();
    if (isset($_SESSION)) {
	ini_set('session.use_cookies', FALSE);
	ini_set('session.use_trans_sid', FALSE);
	ini_set('arg_separator.output', '&amp;');
	ini_set('url_rewriter.tags', '');

	//session_start();
	//unset($_SESSION);
	//unset($_GET['PHPSESSID']);
	//define('SID', NULL);
	session_destroy();
	//$level = 0;
	unset($level);
    }
    $deco = TRUE;
} else
    $deco = FALSE;

require('top.inc.php');

echo "\t<h2>Déconnexion</h2>\n";

if (!$deco && $level == 0)
    echo "\t<p>Vous n'êtes pas connecté(e).</p>\n";
else {
    if ($deco)
	echo "\t<p>Déconnexion effectuée. Vous n'êtes plus connecté(e).</p>\n";
    else if (isset($_POST['annuler']))
	echo "\t<p>Déconnexion annulée. Vous êtes toujours connecté(e).</p>\n";
    else {
?>
	<form action="<?php echo $self . $urladd; ?>" method="post">
	  <p>Souhaitez-vous terminer votre session&nbsp;?</p>
	  <p>
	    <input type="submit" name="confirmer" value="Confirmer" />
	    <input type="submit" name="annuler" value="Annuler" />
	  </p>
	</form>
<?php
    }
}

require('bottom.inc.php');

?>
