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

<!-- Formulaire pour rechercher un livre dans la base de donnée -->

	<h2>Rechercher un livre</h2>

<?php

// On récupère les données du formulaire
$titre = (string) @$_POST['titre'];
$annee = (string) @$_POST['annee'];
$ville = (string) @$_POST['ville'];
$numEditeur = (string) @$_POST['numEditeur'];
$numAuteur = (string) @$_POST['numAuteur'];

// On vérifie si un des champs est rempli
// Si aucun n'est rempli, on affiche simplement le formulaire de recherche
if( $titre == '' && $ville == '' && ($numEditeur == '0' || $numEditeur == '') && ($numAuteur == '0' || $numAuteur == '') 
	&& ($annee == '' || !is_numeric($annee) || strlen($annee) != 4) )
{
	$error=0;
	
	if($annee != '' && (!is_numeric($annee) || strlen($annee) != 4) )
	{
		echo "\t<p>Le champ \"Annee\" n'est pas valide.</p>\n";
		$error=1;
	}
	
	// On crée la liste des éditeurs existants
	$conn=connect();
	$req=@pg_query( $conn, "SELECT * FROM Editeur ORDER BY nomEditeur" );
	if(!$req)
		db_error();

	$liste = "\t\t    <option value=\"0\"></option>\n";
	while( $ligne = @pg_fetch_array($req) )
		$liste .= "\t\t    <option value=\"$ligne[idediteur]\">$ligne[nomediteur]</option>\n";
		
	// On crée la liste des auteurs
	$req=@pg_query( $conn, "SELECT * FROM Auteur ORDER BY nomAuteur,initialesPrenoms" );
	if(!$req)
		db_error();

	$liste2 = "\t\t    <option value=\"0\"></option>\n";
	while( $ligne = @pg_fetch_array($req) )
		$liste2 .= "\t\t    <option value=\"$ligne[idauteur]\">$ligne[initialesprenoms]. $ligne[nomauteur]</option>\n";
		
	disconnect();
	
?>
	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Titre de l'ouvrage&nbsp;:</th>
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
		<th>Année de parution&nbsp;:</th>
		<td><input type="text" name="annee" maxlength="4" /> <em>(4 chiffres)</em></td>
	      </tr>
	      <tr>
		<th>Ville d'édition&nbsp;:</th>
		<td><input type="text" name="ville" /></td>
	      </tr>
	      <tr>
		<th>Nom de l'éditeur&nbsp;:</th>
		<td>
		  <select name="numEditeur" size="1">
<?php echo $liste; ?>
		  </select>
		</td>
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
	
	$reqOuvrage = "Ouvrage a";
	if($titre != '')
		$reqTitre = "a.titreOuvrage ILIKE '$titre'";
	else
		$reqTitre = "FALSE";

	if($annee != '' && is_numeric($annee))
		$reqAnnee = "a.annee = '$annee'";
	else
		$reqAnnee = "FALSE";

	if($ville != '')
		$reqVille = "a.ville ILIKE '$ville'";
	else
		$reqVille = "FALSE";

	if($numEditeur != '' && $numEditeur != '0')
		$reqNumEditeur = "a.idEditeur = '$numEditeur'";
	else
		$reqNumEditeur = "FALSE";

	if($numAuteur != ''&& $numAuteur != '0')
	{
		$reqNumAuteur = "(b.idAuteur = '$numAuteur' AND b.idOuvrage = a.idOuvrage)";
		$reqOuvrage = $reqOuvrage . ",AuteurOuvrage b";
	}
	else
		$reqNumAuteur = "FALSE";	
		
	$conn=connect();
	// On fait la requete
	$req=@pg_query( $conn, "SELECT DISTINCT a.idOuvrage FROM $reqOuvrage WHERE $reqTitre OR $reqAnnee OR 
			$reqVille OR $reqNumEditeur OR $reqNumAuteur" );
	if(!$req)
		db_error();

	$numRes=0;
	
	while( $ligne = @pg_fetch_array($req) )
	{
		$res[$numRes]=$ligne['idouvrage'];
		$numRes++;	
	}
	
	if($numRes == 0)
		echo "\t<p>Pas de réponses à votre recherche.</p>";
	else
		echo "\t<p>Le serveur a trouvé $numRes réponse(s) à votre recherche&nbsp;:</p>\n";

	// On affiche les résultats
	for($i=0; $i < $numRes; $i++)
	{
		// On récupère les infos sur l'ouvrage
		$req = @pg_query( $conn, "SELECT * FROM Ouvrage WHERE idOuvrage = '$res[$i]'");
		if(!$req)
			db_error();
		$ligne = @pg_fetch_array($req);
	
		// On récupère les auteurs
		$reqAut = @pg_query( $conn, "SELECT a.* FROM Auteur a,AuteurOuvrage b
			WHERE b.idOuvrage = '$res[$i]' AND a.idAuteur = b.idAuteur ORDER BY nomAuteur,initialesPrenoms");
			
		// On récupère l'éditeur
		$reqEditeur = @pg_query( $conn, "SELECT nomEditeur FROM Editeur WHERE idEditeur = '$ligne[idediteur]'");
		$ligneEditeur = @pg_fetch_array($reqEditeur);
?>
	<table class="center">
	  <tbody>
	    <tr>
	      <th>Titre de l'ouvrage&nbsp;:</th>
	      <td><?php
	if(strtolower($ligne['titreouvrage']) == strtolower($titre)) echo '<strong>';
	echo $ligne['titreouvrage'];
	if(strtolower($ligne['titreouvrage']) == strtolower($titre)) echo '</strong>';
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
	      <th>Année de parution&nbsp;:</th>
	      <td><?php
	if($ligne['annee'] == $annee) echo '<strong>';
	echo $ligne['annee'];
	if($ligne['annee'] == $annee) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Ville d'édition&nbsp;:</th>
	      <td><?php
	if(strtolower($ligne['ville']) == strtolower($ville)) echo '<strong>';
	echo $ligne['ville'];
	if(strtolower($ligne['ville']) == strtolower($ville)) echo '</strong>';
?></td>
	    </tr>
	    <tr>
	      <th>Nom de l'éditeur&nbsp;:</th>
	      <td><?php
	if($ligne['idediteur'] == $numEditeur) echo '<strong>';
	echo $ligneEditeur['nomediteur'];
	if($ligne['idediteur'] == $numEditeur) echo '</strong>';
?></td>
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
