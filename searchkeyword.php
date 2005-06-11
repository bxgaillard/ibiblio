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

<!-- Formulaire pour rechercher un article grâce aux mots-clés -->

	<h2>Thésaurus</h2>

<?php

// On récupère le mot-clé
$motCle = (string) @$_POST['motCle'];

$error=0;

// On vérifie si un des champs est rempli
// S'il sont vides on affiche le formulare
if( isset($_POST['submitMotCle']) && ( $motCle == '' || $motCle == ' ') )
{
	echo "\t<p>Le champ \"Mot clé\" n'est pas rempli.</p>\n";
	$error=1;
}

if( isset($_POST['submitListe']) && !isset($_POST['listeMotCle']) )
{
	echo "\t<p>Aucun mot clé n'est sélectionné.</p>\n";
	$error=1;
}

if( (!isset($_POST['submitMotCle']) && !isset($_POST['submitListe'])) || $error) 
{
	// On crée la liste des mots-clés
	$conn=connect();
	$req=@pg_query( $conn, "SELECT * FROM Thesaurus ORDER BY nomMotCle" );
	if(!$req)
		db_error();

	$liste = '';
	while( $ligne = @pg_fetch_array($req) )
		$liste .= "\t      <option value=\"$ligne[idmotcle]\">$ligne[nommotcle]</option>";
		
	disconnect();
	
	if (!empty($liste)) {
?>
	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <p>
	    Recherche par mot clé (il peut s'agit d'une partie du mot-clé)&nbsp;: <input type="text" name="motCle" />
	    <input type="submit" name="submitMotCle" value="Rechercher" />
	    <input type="reset" />
	  </p>
	</form>
	<p>
	  Recherche avec la liste des mots clés&nbsp;:<br />
	  (vous pouvez en sélectionner plusieurs en appuyant sur la touche "Ctrl" ou "Maj")
	</p>
	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <p>
	    <select name="listeMotCle[]" size="10" multiple="multiple">
<?php echo $liste; ?>
	    </select>
	  </p>
	  <p>
	    <input type="submit" name="submitListe" value="Rechercher" />
	    <input type="reset" />
	  </p>
	</form>

<?php
	} else
		echo "\t<p>Il n'y a aucun mot-clé référencé.</p>\n";
}
	
$numRes=0;	

if(!$error && isset($_POST['submitMotCle']) && $motCle != '' && $motCle != ' ')
{
	// On fait une recherche sur un mot-clé entré par l'utilisateur
	$conn=connect();
	// On fait la requete
	$req=@pg_query( $conn, "SELECT DISTINCT a.idArticle FROM Article a,DescriptionArticle c,Thesaurus d
			WHERE d.nomMotCle ILIKE '%$motCle%' AND d.idMotCle = c.idMotCle AND c.idArticle = a.idArticle" );
	if(!$req)
		db_error();

	// On met les résultats dans le tableau $res
	$res = array();
	while( $ligne = @pg_fetch_array($req) )
	{
		$res[$numRes]=$ligne['idarticle'];
		$numRes++;	
	}
}
else if( isset($_POST['submitListe']) && isset($_POST['listeMotCle']) )
{
	// On fait la recherche sur la liste de mots clés
	
	// On récupère la liste des identifiants de mots clés
	$mots = $_POST['listeMotCle'];
		
	// On recherche d'abord la liste des mots-clés
	$conn=connect();
	$req=@pg_query( $conn, "SELECT DISTINCT idMotCle FROM DescriptionArticle ORDER BY idMotCle" );
	if(!$req)
		db_error();

	$requete = "SELECT DISTINCT a.idArticle FROM Article a,DescriptionArticle b WHERE b.idArticle = a.idArticle AND ( FALSE ";
	
	$i = 0;
	while( $ligne = pg_fetch_array($req) )
	{
		if (in_array($ligne['idmotcle'], $mots, TRUE))
			$requete .= " OR b.idMotCle = '$ligne[idmotcle]'";
	}
	
	$requete .= ')';
	
	// On fait la requete
	$req=@pg_query( $conn, "$requete" );
	if(!$req)
		db_error();

	// On met les résultats dans le tableau $res
	$res = array();
	while( $ligne = pg_fetch_array($req) )
	{
		$res[$numRes]=$ligne['idarticle'];
		$numRes++;	
	}
}

// On affiche les résultats
if( (isset($_POST['submitMotCle']) || isset($_POST['submitListe'])) && !$error)
{
	if($numRes == 0)
		echo "\t<p>Pas de réponses à votre recherche.</p>\n";
	else
		echo "\t<p>Le serveur a trouvé $numRes réponse(s) à votre recherche&nbsp;:</p>\n";
	
	for($i=0; $i < $numRes; $i++)
	{
		// On récupère les infos sur l'article
		$req = @pg_query( $conn, "SELECT * FROM Article WHERE idArticle = '$res[$i]'");
		if(!$req)
			db_error();
		$ligne = pg_fetch_array($req);
	
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
	      <td><?php echo $ligne['titrearticle']; ?></td>
	    </tr>
	    <tr>
	      <th>Auteurs&nbsp;:</th>
	      <td>
<?php
		while($ligneAut = pg_fetch_array($reqAut))
			echo "\t\t$ligneAut[initialesprenoms]. $ligneAut[nomauteur]<br />\n";
?>	  
	      </td>
	    </tr>
	    <tr>
	      <th>Date de publication&nbsp;:</th>
	      <td><?php
	$date2 = $ligne['date'];
	$date2 = $date2{8} . $date2{9} . "/" . $date2{5} . $date2{6} . "/" . $date2{0} . $date2{1} . $date2{2} . $date2{3};
	echo $date2;
?></td>
	    </tr>
	    <tr>
	      <th>Numéro de volume&nbsp;:</th>
	      <td><?php echo $ligne['novolume']; ?></td>
	    </tr>
	    <tr>
	      <th>Numéro de série&nbsp;:</th>
	      <td><?php echo $ligne['noserie']; ?></td>
	    </tr>
	    <tr>
	      <th>Page de début&nbsp;:</th>
	      <td><?php echo $ligne['pagedebut']; ?></td>
	    </tr>
	    <tr>
	      <th>Page de fin&nbsp;:</th>
	      <td><?php echo $ligne['pagefin']; ?></td>
	    </tr>
	    <tr>
	      <th>Nom de la revue&nbsp;:</th>
	      <td><?php echo $ligneRevue['nomrevue']; ?></td>
	    </tr>
	    <tr>
	      <th>Mots clés&nbsp;:</th>
	      <td>
<?php
		while($ligneMot = pg_fetch_array($reqMot))
		{
			if( isset($_POST['submitMotCle']) )
			{
				echo "\t\t";
				$pos = strpos( strtolower($ligneMot['nommotcle']), strtolower($motCle) );
				if(!($pos === false))
					echo '<strong>';
				echo $ligneMot['nommotcle'];
				if(!($pos === false))
					echo '</strong>';
				echo "<br />\n";
			}
			else
			{
				echo "\t\t";
				if (in_array($ligneMot['idmotcle'], $mots, TRUE))
					echo '<strong>';
				echo $ligneMot['nommotcle'];
				if (in_array($ligneMot['idmotcle'], $mots, TRUE))
					echo '</strong>';
				echo "<br />\n";
			}
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
