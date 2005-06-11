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

<!-- Formulaire pour l'ajout d'un livre dans la base de donnée -->

	<h2>Ajout d'un livre</h2>

<?php

require_rights(1);

// On récupère les données du formulaire
$titre = (string) @$_POST['titre'];
$annee = (string) @$_POST['annee'];
$ville = (string) @$_POST['ville'];
$nomEditeur = (string) @$_POST['nomEditeur'];
$numEditeur = (string) @$_POST['numEditeur'];

$nomAuteurs = array();
$numAuteurs = array();
$iniAuteurs = array();

$conn=connect();
	
$i = 0;
$error = 0;

// On récupère les anciens auteurs
while(isset($_POST['nomAuteur' . $i]) && isset($_POST['numAuteur' . $i]) && isset($_POST['iniAuteur' . $i]))
{
	$nomAuteurs[] = $_POST['nomAuteur' . $i];
	$iniAuteurs[] = $_POST['iniAuteur' . $i];
	$numAuteurs[] = $_POST['numAuteur' . $i++];
}

// Si le bouton ajouter un auteur a été appelé
if (isset($_POST['AjouteAuteur']))
{
	echo "\t<p>\n";
	if($_POST['numNouvAuteur'] != 0)
	{
		if(in_array($_POST['numNouvAuteur'], $numAuteurs))
		{
			echo "Cet auteur est déjà dans la liste.<br />";
			$error=1;
		}
		
		if(!$error)
		{
			// On cherche les infos sur l'auteur
			$req=@pg_query( $conn, "SELECT * FROM Auteur WHERE idAuteur = '$_POST[numNouvAuteur]'");
			if(!$req)
				db_error();
			$ligne = pg_fetch_array($req);
			if(!$ligne)
				db_error();
			$nomAuteurs[] = $ligne['nomauteur'];
			$iniAuteurs[] = $ligne['initialesprenoms'];
		}
	}
	else
	{
		for($i=0; !$error && $i < count($numAuteurs); $i++)
		{
			if(strtolower($_POST['nomNouvAuteur']) == strtolower($nomAuteurs[$i])
				 && strtolower($_POST['iniNouvAuteur']) == strtolower($iniAuteurs[$i]))
			{
				echo "Cet auteur est déjà dans la liste.<br />";
				$error=1;
			}
		}
		
		if(!$error)
		{
			if($_POST['nomNouvAuteur'] != '')
				$nomAuteurs[] = $_POST['nomNouvAuteur'];
			else
			{
				echo "\tLe champ \"Nom de l'auteur\" n'est pas rempli.<br />\n";
				$error=1;
			}
			if($_POST['iniNouvAuteur'] != '')
				$iniAuteurs[] = $_POST['iniNouvAuteur'];
			else
			{
				echo "\tLe champ \"Initiales du prénom de l'auteur\" n'est pas rempli.<br />\n";
				$error=1;
			}
			if($error)
			{
				unset($nomAuteurs[$i]);
				unset($iniAuteurs[$i]);
			}
			else
			{
				unset($_POST['iniNouvAuteur']);
				unset($_POST['nomNouvAuteur']);
			}
		}	
	}
	if(!$error)
		$numAuteurs[] = $_POST['numNouvAuteur'];
		
	$max = count($numAuteurs);
	echo "\t</p>\n";
}
else
{
	$max = count($numAuteurs);
	
	// Si un bouton supprimer a été appuyé
	for($i = 0; !$error && $i < count($numAuteurs); $i++)
	{
		if(isset($_POST['SupprAuteur' . $i]))
		{
			unset($numAuteurs[$i]);
			unset($nomAuteurs[$i]);
			unset($iniAuteurs[$i]);
		}
	}
}

// On vérifie si les champs sont remplis
if( $error == 0 && isset($_POST['submit']) && count($numAuteurs) > 0 && $titre != '' && $annee != '' && is_numeric($annee) && strlen($annee) == 4
	 && $ville != '' && ($numEditeur != '0' || $nomEditeur != '') )
{
	// Ajout du livre à la base de données
	
	$error=0;

	// Si l'éditeur n'est pas encore dans la table, on l'ajoute
	if($numEditeur == 0)
	{
		// On vérifie que l'éditeur n'existe pas encore
		$req=@pg_query( $conn, "SELECT * FROM Editeur WHERE nomEditeur = '$nomEditeur'");
		if(!$req)
			db_error();
		$ligne = pg_fetch_array($req);
		if(!$ligne)
		{
			// Ajout de la revue
			$req=@pg_query( $conn, "INSERT INTO Editeur ( nomEditeur ) VALUES ('$nomEditeur')");
			if(!$req)	
				db_error();
			
			// On va récupérer le numéro de la revue que l'on vient d'ajouter
			$req=@pg_query( $conn, "SELECT idEditeur FROM Editeur ORDER BY idEditeur DESC");
			if(!$req)	
				db_error();
			$ligne = pg_fetch_array($req);
		}
		$numEditeur = $ligne['idediteur'];
	}		
		
	// On ajoute le nouvel ouvrage
	$req=@pg_query( $conn, "INSERT INTO Ouvrage ( titreOuvrage, idEditeur , ville , annee  )
			VALUES ('$titre','$numEditeur','$ville','$annee')");
	if(!$req)
		db_error();
	
	// On récupère le numéro de l'ouvrage
	$req=@pg_query( $conn, "SELECT idOuvrage FROM Ouvrage ORDER BY idOuvrage DESC");
	if(!$req)
		db_error();
	$ligne = pg_fetch_array($req);
	$idOuvrage = $ligne['idouvrage'];
	
	// On ajoute les auteurs
	for($i=0; $i < count($numAuteurs); $i++)
	{
		if($numAuteurs[$i] == 0)
		{
			// On regarde si l'auteur existe déjà dans la base
			$req=@pg_query( $conn, "SELECT * FROM Auteur WHERE nomAuteur ILIKE '$nomAuteurs[$i]'
				 AND initialesPrenoms ILIKE '$iniAuteurs[$i]'");
			if(!$req)
				db_error();
			$ligne = pg_fetch_array($req);
			if(!$ligne) //L'auteur n'existe pas encore
			{
				// On ajoute l'auteur dans la base
				$req=@pg_query( $conn, "INSERT INTO Auteur ( nomAuteur, initialesPrenoms ) 
					VALUES ('$nomAuteurs[$i]','$iniAuteurs[$i]')");
				if(!$req)
					db_error();
				
				// On récupère le numéro de l'auteur
				$req=@pg_query( $conn, "SELECT idAuteur FROM Auteur ORDER BY idAuteur DESC");
				if(!$req)
					db_error();
				$ligne = pg_fetch_array($req);
			}
			$id = $ligne['idauteur'];
		}
		else
			$id = $numAuteurs[$i];

		// On ajoute la relation entre le livre et l'auteur
		$req=@pg_query( $conn, "INSERT INTO AuteurOuvrage ( idAuteur, idOuvrage ) VALUES ('$id','$idOuvrage')");
		if(!$req)
			db_error();
	}
	
	echo "<p>L'ajout s'est bien passé. Voici le récapitulatif&nbsp;:</p>";
	
	// On récupère les infos sur l'ouvrage
	$req = @pg_query( $conn, "SELECT * FROM Ouvrage WHERE idOuvrage = '$idOuvrage'");
	if(!$req)
		db_error();
	$ligne = pg_fetch_array($req);

	// On récupère les auteurs
	$reqAut = @pg_query( $conn, "SELECT a.nomAuteur,a.initialesPrenoms FROM Auteur a,AuteurOuvrage b
		WHERE b.idOuvrage = '$idOuvrage' AND a.idAuteur = b.idAuteur ORDER BY nomAuteur,initialesPrenoms");
		
	// On récupère l'éditeur
	$reqEditeur = @pg_query( $conn, "SELECT nomEditeur FROM Editeur WHERE idEditeur = '$ligne[idediteur]'");
	$ligneEditeur = @pg_fetch_array($reqEditeur);
?>
	<table class="center">
	  <tbody>
	    <tr>
	      <th>Titre de l'ouvrage&nbsp;:</th>
	      <td><?php echo $ligne['titreouvrage']; ?></td>
	    </tr>
	    <tr>
	      <th>Auteurs&nbsp;:</th>
	      <td>
<?php
	while($ligneAut = pg_fetch_array($reqAut))
		echo "\t\t" . $ligneAut['initialesprenoms'] . '. ' . $ligneAut['nomauteur'] . "<br />\n";
?>
	      </td>
	    </tr>
	    <tr>
	      <th>Année de parution&nbsp;:</th>
	      <td><?php echo $ligne['annee']; ?></td>
	    </tr>
	    <tr>
	      <th>Ville d'édition&nbsp;:</th>
	      <td><?php echo $ligne['ville']; ?></td>
	    </tr>
	    <tr>
	      <th>Nom de l'éditeur&nbsp;:</th>
	      <td><?php echo $ligneEditeur['nomediteur']; ?></td>
	    </tr>
	  </tbody>
	</table>

	<p><a href="<?php echo $self . $urladd ?>">Ajouter un nouveau livre</a></p>
<?php
	@disconnect();
}

// S'ils ne sont pas tous remplis, on vérifie lesquels sont remplis et on demande de compléter les autres
// Si aucun n'est rempli, on affiche simplement le formulaire d'installation
else
{
	if( isset($_POST['submit']) && ($titre != '' || $annee != '' || $ville != '' || ($numEditeur != '0' && $nomEditeur != '')) )
	{
		echo "\t<p>\n";
		if($titre == '')
			echo "\t  Le champ \"Titre\" n'est pas rempli.<br />\n";
		if($annee == '')
			echo "\t  Le champ \"Annee\" n'est pas rempli.<br />\n";
		else if(!is_numeric($annee) || strlen($annee) != 4)
			echo "\t  Le champ \"Annee\" n'est pas valide.<br />\n";
		if($ville == '')
			echo "\t  Le champ \"Ville\" n'est pas rempli.<br />\n";
		if($numEditeur == '0' && $nomEditeur == '')
			echo "\t  Vous devez soit choisir un éditeur dans la liste, soit remplir le champs \"Nom de l'éditeur\".<br />\n";
		if( count($numAuteurs) <= 0 )
			echo "\t  Vous devez avoir au moins un auteur.<br />\n";
		echo "\t</p>\n";
	}
	
	// On crée la liste des éditeurs existants
	$req=@pg_query( $conn, "SELECT * FROM Editeur ORDER BY nomEditeur" );
	if(!$req)
		db_error();
	
	$liste = "\t\t    <option value=\"0\">Nouveau</option>\n";
	while( $ligne = @pg_fetch_array($req) )
		$liste .= "\t\t    <option value=\"$ligne[idediteur]\">$ligne[nomediteur]</option>\n";
		
	// On crée la liste des auteurs
	$req=@pg_query( $conn, "SELECT * FROM Auteur ORDER BY nomAuteur,initialesPrenoms" );
	if(!$req)
		db_error();
	
	$liste2 = "\t\t    <option value=\"0\">Nouveau</option>\n";
	while( $ligne = @pg_fetch_array($req) )
		$liste2 .= "\t\t    <option value=\"$ligne[idauteur]\">$ligne[initialesprenoms]. $ligne[nomauteur]</option>\n";
	
	disconnect();
?>

	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Titre de l'ouvrage&nbsp;:</th>
		<td><input type="text" name="titre" value="<? echo "$titre"; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<td colspan="2">&nbsp;</td>
	      </tr>

	      <tr>
		<th>Auteurs&nbsp;:</th>
		<td>
<?php
$j=0;
for($i=0; $i < $max; $i++)
{
	if(isset($nomAuteurs[$i]))
	{	
		echo <<<EOF
		  $iniAuteurs[$i]. $nomAuteurs[$i] <input type="submit" name="SupprAuteur$j" value="Supprimer" /><br />
		  <input type="hidden" name="nomAuteur$j" value="$nomAuteurs[$i]" />
		  <input type="hidden" name="iniAuteur$j" value="$iniAuteurs[$i]" />
		  <input type="hidden" name="numAuteur$j" value="$numAuteurs[$i]" />
EOF;
		$j++;
	}
}
if($j == 0)
	echo "\t\t  <em>Aucun</em>";
?>
		</td>
	      </tr>
	      <tr>
		<th>Ajouter un auteur&nbsp;:</th>
		<td>
		  <select name="numNouvAuteur" size="1">
<?php echo $liste2; ?>
		  </select>
		  Nom&nbsp;: 
		  <input type="text" name="nomNouvAuteur" value="<?php echo @$_POST['nomNouvAuteur']; ?>" />
		</td>
	      </tr>
	      <tr>
		<td></td>
		<td>
		  Initiales du prénom&nbsp;:
		  <input type="text" name="iniNouvAuteur" value="<?php echo @$_POST['iniNouvAuteur']; ?>" />
		  <input type="submit" name="AjouteAuteur" value="Ajouter" />
		</td>
	      </tr>
	      <tr>
		<td colspan="2">&nbsp;</td>
	      </tr>
	      <tr>
		<th>Année de parution&nbsp;:</th>
		<td><input type="text" name="annee" maxlength="4" value="<?php echo "$annee"; ?>" /> <em>obligatoire (4 chiffres)</em></td>
	      </tr>
	      <tr>
		<th>Ville d'édition&nbsp;:</th>
		<td><input type="text" name="ville" value="<?php echo "$ville"; ?>" /> <em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Nom de l'éditeur&nbsp;:</th>
		<td>
		  <select name="numEditeur" size="1">
<?php echo $liste; ?>
		  </select>
<!-- A afficher que si la liste est à 0 --> 
		  <input type="text" name="nomEditeur" value="<?php echo $nomEditeur; ?>" />
		  <em>obligatoire</em>
		</td>
	      </tr>
	    </tbody>
	  </table>

	  <p><input type="submit" name="submit" value="Ajouter le livre" /></p>
	</form>

<?php
}

require('bottom.inc.php');

?>
