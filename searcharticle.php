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

<!-- Formulaire pour rechercher un article dans la base de donnée -->

	<h2>Rechercher un article</h2>

<?php

function verifDate($date)
{
    if (strlen($date) == 10 &&
	is_numeric($date{0}) && is_numeric($date{1}) && $date{2} == '/' &&
	is_numeric($date{3}) && is_numeric($date{4}) && $date{5} == '/' &&
	is_numeric($date{6}) && is_numeric($date{7}) &&
	is_numeric($date{8}) && is_numeric($date{9})) {
	list($day, $month, $year) = explode('/', $date, 3);
	if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12)
	    return TRUE;
    }
    return FALSE;
}

// On récupère les données du formulaire
$titre = (string) @$_POST['titre'];
$date = (string) @$_POST['date'];
$numRevue = (string) @$_POST['numRevue'];
$numVolume = (string) @$_POST['numVolume'];
$numSerie = (string) @$_POST['numSerie'];
$pageDeb = (string) @$_POST['pageDeb'];
$pageFin = (string) @$_POST['pageFin'];
$motCle = (string) @$_POST['motCle'];
$numAuteur = (string) @$_POST['numAuteur'];


// On vérifie si un des champs est rempli
// Si aucun n'est rempli, on affiche simplement le formulaire de recherche
if( $titre == '' && ($date == '' || !verifDate($date)) && ($numRevue == '0' || $numRevue == '') && ($numVolume == ''
	 || !is_numeric($numVolume)) && ($numSerie == '' || !is_numeric($numSerie)) && ($pageDeb == '' 
	 || !is_numeric($pageDeb)) && ($pageFin == '' || !is_numeric($pageFin)) && ($numAuteur == '0' || $numAuteur == '')
	 && $motCle =='' )
{
	$error=0;
	
	echo "\t<p>";
	if($date != '' && !verifDate($date) )
	{
		echo "\t  Le champ \"Date\" n'est pas valide.<br />\n";
		$error=1;
	}
	if($numVolume != '' && !is_numeric($numVolume) )
	{
		echo "\t  Le champ \"Numéro de volume\" n'est pas valide.<br />\n";
		$error=1;
	}
	if($numSerie != '' && !is_numeric($numSerie) )
	{
		echo "\t  Le champ \"Numéro de série\" n'est pas valide.<br />\n";
		$error=1;
	}
	if($pageDeb != '' && !is_numeric($pageDeb) )
	{
		echo "\t  Le champ \"Page de début\" n'est pas valide.<br />\n";
		$error=1;
	}
	if($pageFin != '' && !is_numeric($pageFin) )
	{
		echo "\t  Le champ \"Page de fin\" n'est pas valide.<br />\n";
		$error=1;
	}
	echo "\t</p>";
	
	// On crée la liste des revues
	$conn=connect();
	$req=@pg_query( $conn, "SELECT * FROM Revue ORDER BY nomRevue" );
	if(!$req)
		db_error();

	$liste = "\t\t    <option value=\"0\"></option>\n";
	while( $ligne = pg_fetch_array($req) )
		$liste .= "\t\t    <option value=\"$ligne[idrevue]\">$ligne[nomrevue]</option>\n";
		
	// On crée la liste des auteurs
	$req=@pg_query( $conn, "SELECT * FROM Auteur ORDER BY nomAuteur,initialesPrenoms" );
	if(!$req)
		db_error();

	$liste2 = "\t\t    <option value=\"0\"></option>\n";
	while( $ligne = pg_fetch_array($req) )
		$liste2 .= "\t\t    <option value=\"$ligne[idauteur]\">$ligne[initialesprenoms]. $ligne[nomauteur]</option>\n";
		
	disconnect();
	
?>
	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Titre de l'article&nbsp;:</th>
		<td><input type="text" name="titre" /></td>
	      </tr>
	      <tr>
		<th>Nom de l'auteur&nbsp;:</th>
		<td>
		  <select name="numAuteur" size="1">
<?php echo $liste2; ?>
		  </select>
		</td>
	      </tr>
	      <tr>
		<th>Date de publication&nbsp;:</th>
		<td><input type="text" name="date" maxlength="10" /> <em>(JJ/MM/AAAA)</em></td>
	      </tr>
	      <tr>
		<th>Numéro de volume&nbsp;:</th>
		<td><input type="text" name="numVolume" /></td>
	      </tr>
	      <tr>
		<th>Numéro de série&nbsp;:</th>
		<td><input type="text" name="numSerie" /></td>
	      </tr>
	      <tr>
		<th>Page de début&nbsp;:</th>
		<td><input type="text" name="pageDeb" /></td>
	      </tr>
	      <tr>
		<th>Page de fin&nbsp;:</th>
		<td><input type="text" name="pageFin" /></td>
	      </tr>
	      <tr>
		<th>Nom de la revue&nbsp;:</th>
		<td>
		  <select name="numRevue" size="1">
<?php echo $liste; ?>
		  </select>
		</td>
	      </tr>
	      <tr>
		<th>Mot clé&nbsp;:</th>
		<td><input type="text" name="motCle" /></td>
	      </tr>
	    </tbody>
	  </table>
	  <p><input type="submit" name="submit" value="Rechercher" /></p>
	</form>

<?php

}
	
else
{
	// On fait la recherche
	
	$reqArticle = "Article a";
	if($titre != '')
		$reqTitre = "a.titreArticle ILIKE '$titre'";
	else
		$reqTitre = "FALSE";

	if($date != '' && verifDate($date))
		$reqDate = "a.date = '$date'";
	else
		$reqDate = "FALSE";

	if($numRevue != '' && $numRevue != '0')
		$reqNumRevue = "a.idRevue = '$numRevue'";
	else
		$reqNumRevue = "FALSE";

	if($numVolume != '' && is_numeric($numVolume))
		$reqNumVolume = "a.noVolume = '$numVolume'";
	else
		$reqNumVolume = "FALSE";

	if($numSerie != '' && is_numeric($numSerie))
		$reqNumSerie = "a.noSerie = '$numSerie'";
	else
		$reqNumSerie = "FALSE";

	if($pageDeb != '' && is_numeric($pageDeb))
		$reqPageDeb = "a.pageDebut = '$pageDeb'";
	else
		$reqPageDeb = "FALSE";

	if($pageFin != '' && is_numeric($pageFin))
		$reqPageFin = "a.pageFin = '$pageFin'";
	else
		$reqPageFin = "FALSE";
	
	if($numAuteur != '' && $numAuteur != '0')
	{
		$reqNumAuteur = "(b.idAuteur = '$numAuteur' AND b.idArticle = a.idArticle)";
		$reqArticle = $reqArticle . ",AuteurArticle b";
	}
	else
		$reqNumAuteur = "FALSE";
		
	if($motCle != '')
	{
		$reqMotCle = "(d.nomMotCle ILIKE '$motCle' and d.idMotCle = c.idMotCle AND c.idArticle = a.idArticle)";
		$reqArticle = $reqArticle . ",DescriptionArticle c,Thesaurus d";
	}
	else
		$reqMotCle = "FALSE";	
		
	$conn=connect();
	// On fait la requete
	$req=@pg_query( $conn, "SELECT DISTINCT a.idArticle FROM $reqArticle WHERE $reqTitre OR $reqDate OR $reqNumRevue OR $reqNumVolume OR
			 $reqNumSerie OR $reqPageDeb OR $reqPageFin OR $reqNumAuteur OR $reqMotCle" );
	if(!$req)
		db_error();

	$numRes=0;
	
	while( $ligne = pg_fetch_array($req) )
	{
		$res[$numRes]=$ligne['idarticle'];
		$numRes++;	
	}
	
	if($numRes == 0)
		echo "\t<p>Pas de réponses à votre recherche.</p>\n";
	else
		echo "\t<p>Le serveur a trouvé $numRes réponse(s) à votre recherche&nbsp;:</p>\n";
	
	
	// On affiche les résultats
	for($i=0; $i < $numRes; $i++)
	{
		// On récupère les infos sur l'article
		$req = @pg_query( $conn, "SELECT * FROM Article WHERE idArticle = '$res[$i]'");
		if(!$req)
			db_error();
		$ligne = @pg_fetch_array($req);
	
		// On récupère les auteurs
		$reqAut = @pg_query( $conn, "SELECT a.* FROM Auteur a,AuteurArticle b
			WHERE b.idArticle = '$res[$i]' AND a.idAuteur = b.idAuteur ORDER BY nomAuteur,initialesPrenoms");
			
		// On récupère la revue
		$reqRevue = @pg_query( $conn, "SELECT nomRevue FROM Revue WHERE idRevue = '$ligne[idrevue]'");
		$ligneRevue = @pg_fetch_array($reqRevue);
		
		// On récupère les mots clés
		$reqMot = @pg_query( $conn, "SELECT a.* FROM Thesaurus a,DescriptionArticle b
			WHERE b.idArticle = '$res[$i]' AND a.idMotCle = b.idMotCle ORDER BY nomMotCle");
?>

	<table class="center">
	  <tbody>
	    <tr>
	      <th>Titre de l'article&nbsp;:</th>
	      <td><?php
	if(strtolower($ligne['titrearticle']) == strtolower($titre)) echo '<strong>';
	echo $ligne['titrearticle'];
	if(strtolower($ligne['titrearticle']) == strtolower($titre)) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Auteurs&nbsp;:</th>
	      <td>
<?php
		while($ligneAut = pg_fetch_array($reqAut))
		{
			echo "\t\t";
			if($ligneAut['idauteur'] == $numAuteur) echo '<strong>';
			echo "$ligneAut[initialesprenoms]. $ligneAut[nomauteur]";
			if($ligneAut['idauteur'] == $numAuteur) echo '</strong>';
			echo "<br />\n";
		}
?>
	      </td>
	    </tr>
	    <tr>
	      <th>Date de publication&nbsp;:</th>
	      <td><?php
	$date2 = $ligne['date'];
	$date2 = $date2{8} . $date2{9} . "/" . $date2{5} . $date2{6} . "/" . $date2{0} . $date2{1} . $date2{2} . $date2{3};
	if($date2 == $date) echo '<strong>';
	echo "$date2";
	if($date2 == $date) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Numéro de volume&nbsp;:</th>
	      <td><?php
	if($ligne['novolume'] == $numVolume) echo '<strong>';
	echo "$ligne[novolume]";
	if($ligne['novolume'] == $numVolume) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Numéro de série&nbsp;:</th>
	      <td><?php
	if($ligne['noserie'] == $numSerie) echo '<strong>';
	echo "$ligne[noserie]";
	if($ligne['noserie'] == $numSerie) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Page de début&nbsp;:</th>
	      <td><?php
	if($ligne['pagedebut'] == $pageDeb) echo '<strong>';
	echo "$ligne[pagedebut]";
	if($ligne['pagedebut'] == $pageDeb) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Page de fin&nbsp;:</th>
	      <td><?php
	if($ligne['pagefin'] == $pageFin) echo '<strong>';
	echo "$ligne[pagefin]";
	if($ligne['pagefin'] == $pageFin) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Nom de la revue&nbsp;:</th>
	      <td><?php
	if($ligne['idrevue'] == $numRevue) echo '<strong>';
	echo "$ligneRevue[nomrevue]";
	if($ligne['idrevue'] == $numRevue) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Mots clés&nbsp;:</th>
	      <td>
<?php
		while($ligneMot = pg_fetch_array($reqMot))
		{
			echo "\t\t";
			if(strtolower($ligneMot['nommotcle']) == strtolower($motCle)) echo '<strong>';
			echo "$ligneMot[nommotcle]";
			if(strtolower($ligneMot['nommotcle']) == strtolower($motCle)) echo '</strong>';
			echo "<br />\n";
		}
?>
	      </td>
	    </tr>
	  </tbody>
	</table>

<?php
	}	
		
	disconnect();	

	echo "\t<p><a href=\"$self$urladd\">Faire une autre recherche</a></p>\n";
}

require('bottom.inc.php');

?>
