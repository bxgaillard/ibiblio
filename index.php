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
	<h2>Accueil</h2>

	<h3>Bienvenue&nbsp;!</h3>

	<p class="left">
	  Bienvenue sur l'interface de gestion d'iBiblio, la base de données
	  bibliographiques qui n'a plus besoin de faire ses preuves&nbsp;! En
	  effet, depuis toujours, nous sommes un acteur de poids dans ce
	  domaine si concurrentiel qu'est la base de données bibliographiques
	  en ligne, marché que nous dominons depuis plusieurs millions
	  d'années.
	</p>

	<p class="left">
	  Forts des retours d'expérience de nos innombrables clients à
	  travers toute la galaxie, nous n'avons cessé de cumuler les succès
	  et d'améliorer sans cesse ce qui est désormais le fleuron de la
	  technologie moderne (la version actuelle atteignant le nombre
	  incroyable de la dizaine de fonctionnalités&nbsp;!).
	</p>

	<p class="left">
	  Grâce à notre incomparable expérience en la matière, nous sommes à
	  même de vous faire bénéficier de ce qui se fait de mieux à l'heure
	  actuelle, et nous vous garantissons qu'essayer notre produit, c'est
	  l'adopter sans hésitation aucune&nbsp;! Comme le dit si bien notre
	  meilleur client, le fameux Alain Cognito&nbsp;: «&nbsp;ça a
	  boulversé ma vie&nbsp;». Nos clients seront les premiers à
	  témoigner en notre faveur pour quelque choix que vous ayiez à
	  effectuer. Nous aurions même eu certains échos nous faisant part
	  du vif intérêt d'un certain William Portes...
	</p>

	<h3>Origine</h3>

	<p class="left">
	  Ce n'est pas sans nostalgie que nous nous rappelons l'origine fort
	  modeste de notre produit d'envergure galactique&nbsp;: ce site a été
	  réalisé dans le cadre du module de base de données de la formation
	  IUP GMI 2<sup>e</sup> année à l'Université Louis Pasteur de
	  Strasbourg. Il a été conçu par Benjamin Gaillard et Nicolas Riegel.
	</p>

	<p class="left">
	  Ce site est sous licence <a
	  href="http://www.gnu.org/copyleft/gpl.html"><acronym xml:lang="en"
	  title="General Public Licence">GPL</acronym></a>. Vous pouvez le
	  télécharger librement <a href="ibiblio.tar.gz">ici</a>.
	</p>
<?php

if (isset($db_configured)) {
    $conn=connect();

    echo "\t<p>\n";

    $req = @pg_query($conn, 'SELECT COUNT(*) FROM Article');
    if (!$req)
	db_error();

    $ligne = pg_fetch_array($req);
    if (!$ligne)
	db_error();
    echo "\t  Nombre d'article référencés : $ligne[0]<br />\n";

    $req = @pg_query($conn, 'SELECT COUNT(*) FROM Ouvrage');
    if (!$req)
	db_error();

    $ligne = pg_fetch_array($req);
    if (!$ligne)
	db_error();
    echo "\t  Nombre d'ouvrages référencés : $ligne[0]<br />\n";

    echo "\t</p>\n";

    disconnect($conn);
}

require('bottom.inc.php');

?>
