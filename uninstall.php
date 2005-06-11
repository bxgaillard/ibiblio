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
if (isset($_POST['confirmer']) && isset($db_configured))
	unset($db_configured);
require('top.inc.php');

?>

<!-- Script pour la désinstallation du site et de la base de donnée -->

	<h2>Désinstallation</h2>

<?php

require_rights(2);

if(isset($_POST['confirmer']))
{
	require('tables.php');
	$res = drop_tables();

	$filename = 'connection.conf.php';

	// On ouvre le fichier en écriture, on le tronque s'il existe et on le crée s'il n'existe pas
	if(!$handle = @fopen($filename, "w"))
		fatal_error("Impossible d'ouvrir le fichier \"$filename\".");

	// On ferme le fichier
	@fclose($handle);

	switch($res)
	{
		case 0 :
		case 1 : echo "\t<p>La désinstallation du site s'est bien déroulée.</p>\n"; break;
		case 2 : echo "\t<p>Erreur lors de la connexion à la base de données. Avez-vous bien installé le site&nbsp;?</p>\n"; break;
		default : echo "\t<p>Une erreur inconnue s'est produite.</p>\n";
	}
}
else if(isset($_POST['annuler']))
	echo "\t<p>Désinstallation annulée.</p>\n";
else
{
?>
	<p>Confirmez-vous la suppression du site&nbsp;?</p>
	<form action="<?php echo $self . $urladd; ?>" method="post">
	  <p>
	    <input type="submit" name="confirmer" value="Confirmer" />
	    <input type="submit" name="annuler" value="Annuler" />
	  </p>
	</form>
<?php
}

require('bottom.inc.php');

?>
