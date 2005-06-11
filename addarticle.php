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
	<h2>Ajout d'un article</h2>

<?php

require_rights(1);

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
$nomRevue = (string) @$_POST['nomRevue'];
$numRevue = (string) @$_POST['numRevue'];
$numVolume = (string) @$_POST['numVolume'];
$numSerie = (string) @$_POST['numSerie'];
$pageDeb = (string) @$_POST['pageDeb'];
$pageFin = (string) @$_POST['pageFin'];
$motCle = (string) @$_POST['motCle'];

$nomAuteurs = array();
$numAuteurs = array();
$iniAuteurs = array();

$conn = connect();

$i = 0;

// On récupère les anciens auteurs
while (isset($_POST['nomAuteur' . $i]) && isset($_POST['numAuteur' . $i]) &&
       isset($_POST['iniAuteur' . $i])) {
    $nomAuteurs[] = $_POST['nomAuteur' . $i];
    $numAuteurs[] = $_POST['numAuteur' . $i];
    $iniAuteurs[] = $_POST['iniAuteur' . $i];
    $i++;
}

$error = FALSE;
// Si le bouton ajouter un auteur a été appelé
if (isset($_POST['AjouteAuteur'])) {
    if ($_POST['numNouvAuteur'] != 0) {
	if (in_array($_POST['numNouvAuteur'], $numAuteurs)) {
	    echo "\tCet auteur est déjà dans la liste.<br />\n";
	    $error = TRUE;
	} else {
	    // On cherche les infos sur l'auteur
	    $req = @pg_query($conn, 'SELECT * FROM Auteur WHERE idAuteur = \''
				  . $_POST['numNouvAuteur'] . '\'');
	    if (!$req)
		db_error();

	    $ligne = pg_fetch_array($req);
	    if (!$ligne)
		db_error();
	    $nomAuteurs[] = $ligne['nomauteur'];
	    $iniAuteurs[] = $ligne['initialesprenoms'];
	}
    } else {
	for($i = 0; $i < count($numAuteurs); $i++) {
	    if (strtolower($_POST['nomNouvAuteur'])
		== strtolower($nomAuteurs[$i]) &&
		strtolower($_POST['iniNouvAuteur'])
		== strtolower($iniAuteurs[$i])) {
		echo "\tCet auteur est déjà dans la liste.<br />\n";
		$error = TRUE;
		break;
	    }
	}

	if (!$error) {
	    if ($_POST['nomNouvAuteur'] !== '')
		$nomAuteurs[] = $_POST['nomNouvAuteur'];
	    else {
		echo "\tLe champ \"Nom de l'auteur\" n'est pas rempli.<br />\n";
		$error = TRUE;
	    }

	    if ($_POST['iniNouvAuteur'] !== '')
		$iniAuteurs[] = $_POST['iniNouvAuteur'];
	    else {
		echo "\tLe champ \"Initiales du prénom de l'auteur\" n'est "
		   . "pas rempli.<br />\n";
		$error = TRUE;
	    }

	    if($error) {
		unset($nomAuteurs[$i]);
		unset($iniAuteurs[$i]);
	    } else {
		unset($_POST['iniNouvAuteur']);
		unset($_POST['nomNouvAuteur']);
	    }
	}	
    }

    if (!$error)
	$numAuteurs[] = $_POST['numNouvAuteur'];

    $max = count($numAuteurs);
} else {
    $max = count($numAuteurs);

    // Si un bouton supprimer a été appuyé
    for($i = 0; !$error && $i < count($numAuteurs); $i++) {
	if(isset($_POST['SupprAuteur' . $i])) {
	    unset($numAuteurs[$i]);
	    unset($nomAuteurs[$i]);
	    unset($iniAuteurs[$i]);
	}
    }
}


// On vérifie si les champs sont remplis
if( !$error && isset($_POST['submit']) && count($numAuteurs) > 0 && $titre != '' && $date != '' && verifDate($date) && ($numRevue != '0' || $nomRevue != '') && $numVolume != ''
	 && is_numeric($numVolume) && $numSerie != '' && is_numeric($numSerie) && $pageDeb != ''
	 && is_numeric($pageDeb) && $pageFin != '' && is_numeric($pageFin) && $pageDeb <= $pageFin )
{
	// Ajout de l'article à la base de données

	$error=0;
	
	// Si la revue n'est pas encore dans la table, on en ajoute une nouvelle
	if($numRevue == 0)
	{
		// On véeifie que la revue n'existe pas encore
		$req=@pg_query( $conn, "SELECT * FROM Revue WHERE nomRevue = '$nomRevue'");
		if(!$req)
			db_error();
		$ligne = pg_fetch_array($req);
		if(!$ligne)
		{
			// Ajout de la revue
			$req=@pg_query( $conn, "INSERT INTO Revue ( nomRevue ) VALUES ('$nomRevue')");
			if(!$req)
				db_error();
			
			// On va récupérer le numéro de la revue que l'on vient d'ajouter
			$req=@pg_query( $conn, "SELECT idRevue FROM Revue ORDER BY idRevue DESC");
			if(!$req)
				db_error();
			$ligne = pg_fetch_array($req);
		}
		$numRevue = $ligne['idrevue'];
	}		
	
	// On ajoute les mots clés
	$motsCles = explode(",", $motCle);
	for($i=0; $i < count($motsCles); $i++)
	{
		if($motsCles[$i] != '' && $motsCles[$i] != '\n' && $motsCles[$i] != '\r' && $motsCles[$i] != '\r\n' && $motsCles[$i] != ' ')
		{
			// On regarde si le mot clé existe déjà dans la base
			$req=@pg_query( $conn, "SELECT * FROM Thesaurus WHERE nomMotCle = '$motsCles[$i]'");
			if(!$req)
				db_error();
			$ligne = pg_fetch_array($req);
			if(!$ligne) //Le mot-cl�n'existe pas encore
			{
				// On ajoute le mot-clé dans la base
				$req=@pg_query( $conn, "INSERT INTO Thesaurus ( nomMotCle ) VALUES ('$motsCles[$i]')");
				if(!$req)
					db_error();
				
				// On r�up�e le numéro du mot-clé
				$req=@pg_query( $conn, "SELECT idMotCle FROM Thesaurus ORDER BY idMotCle DESC");
				if(!$req)
					db_error();
				$ligne = pg_fetch_array($req);
			}
			$id[$i] = $ligne['idmotcle'];
		}
		else
			$id[$i] = 0;
	}
	
	// On ajoute le nouvel article
	$req=@pg_query( $conn, "INSERT INTO Article ( titreArticle, idRevue , noVolume , noSerie , pageDebut , pageFin , date )
			VALUES ('$titre','$numRevue','$numVolume','$numSerie','$pageDeb','$pageFin', '$date')");
	if(!$req)
		db_error();
	
	// On récupère le numéro de l'article
	$req=@pg_query( $conn, "SELECT idArticle FROM Article ORDER BY idArticle DESC");
	if(!$req)
		db_error();
	$ligne = pg_fetch_array($req);
	$idArticle = $ligne['idarticle'];
	
	// On rajoute les liens entre les mot clés et l'article dans DescriptionArticle
	for($i=0; $i < count($motsCles); $i++)
	{
		if($id[$i] != 0)
		{
			$req=@pg_query( $conn, "INSERT INTO DescriptionArticle ( idArticle, idMotCle ) VALUES ('$idArticle','$id[$i]')");
			if(!$req)
				db_error();
		}
	}	
	
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

		// On ajoute la relation entre l'article et l'auteur
		$req=@pg_query( $conn, "INSERT INTO AuteurArticle ( idAuteur, idArticle ) VALUES ('$id','$idArticle')");
		if(!$req)
			db_error();
	}
		
	echo "\t<p>L'ajout s'est bien passé. Voici le récapitulatif&nbsp;:</p>\n";
		
	// On récupère les infos sur l'article
	$req = @pg_query( $conn, "SELECT * FROM Article WHERE idArticle = '$idArticle'");
	if(!$req)
		db_error();
	$ligne = pg_fetch_array($req);

	// On récupère les auteurs
	$reqAut = @pg_query( $conn, "SELECT a.nomAuteur,a.initialesPrenoms FROM Auteur a,AuteurArticle b
		WHERE b.idArticle = '$idArticle' AND a.idAuteur = b.idAuteur ORDER BY nomAuteur,initialesPrenoms");
		
	// On récupère le nom de la revue
	$reqRevue = @pg_query( $conn, "SELECT nomRevue FROM Revue WHERE idRevue = '$ligne[idrevue]'");
	$ligneRevue = pg_fetch_array($reqRevue);
	
	// On récupère les mots clés
	$reqMots = @pg_query( $conn, "SELECT nomMotCle FROM Thesaurus a,DescriptionArticle b
		WHERE b.idArticle = '$idArticle' AND a.idMotCle = b.idMotCle ORDER BY nomMotCle");
	
?>
	<table class="center">
	  <tbody>
	    <tr>
	      <th>Titre de l'article&nbsp;:</th>
	      <td><? echo "$ligne[titrearticle]"; ?></td>
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
	      <th>Nom de la revue&nbsp;:</th>
	      <td><?php echo $ligneRevue['nomrevue']; ?></td>
	    </tr>
	    <tr>
	      <th>Numéro de volume&nbsp;:</th>
	      <td><?php echo "$ligne[novolume]"; ?></td>
	    </tr>
	    <tr>
	      <th>Numéro de serie&nbsp;:</th>
	      <td><?php echo "$ligne[noserie]"; ?></td>
	    </tr>
	    <tr>
	      <th>Page de début&nbsp;:</th>
	      <td><?php echo "$ligne[pagedebut]"; ?></td>
	    </tr>
	    <tr>
	      <th>Page de fin&nbsp;:</th>
	      <td><?php echo "$ligne[pagefin]"; ?></td>
	    </tr>
	    <tr>
	      <th>Date de publication&nbsp;:</th>
	      <td><?php
	$date = $ligne['date'];
	echo $date{8} . $date{9} . '/' . $date{5} . $date{6} . '/' . $date{0} . $date{1} . $date{2} . $date{3};
?></td>
	    </tr>
	    <tr>
	      <th>Mots clés&nbsp;:</th>
	      <td>
<?php
	while($ligneMots = pg_fetch_array($reqMots))
		echo "\t\t" . $ligneMots['nommotcle'] . "<br />\n";
?>
	      </td>
	    </tr>
	  </tbody>
	</table>

	<p><a href="<?php echo $self . $urladd; ?>">Ajouter un nouvel article</a></p>
<?php
	disconnect();
}
// S'ils ne sont pas tous remplis, on vérifie lesquels sont remplis et on demande de compléter les autres
// Si aucun n'est rempli, on affiche simplement le formulaire d'installation
else
{
	if( isset($_POST['submit']) && (count($numAuteurs) == 0 || $titre == '' || $date == '' || ($numRevue == '0' && $nomRevue == '') || $numVolume == ''
		|| $numSerie == '' || $pageDeb == '' || $pageFin == '' || ($pageDeb != '' && $pageFin != '' && $pageFin < $pageDeb) ))
	{
		echo "\t<p>\n";
	 	if($titre == '')
			echo "\t  <strong class=\"error\">Le champ \"Titre\" n'est pas rempli.</strong><br />\n";
		if($numRevue == '0' && $nomRevue == '')
			echo "\t  <strong class=\"error\">Vous devez soit choisir une revue dans la liste, soit remplir le champs \"Nom de la revue\".</strong><br />\n";
		if($numVolume == '')
			echo "\t  <strong class=\"error\">Le champ \"Numéro de volume\" n'est pas rempli.</strong><br />\n";
		elseif(!is_numeric($numVolume))
			echo "\t  <strong class=\"error\">Le champ \"Numéro de volume\" n'est pas valide.</strong><br />\n";
		if($numSerie == '')
			echo "\t  <strong class=\"error\">Le champ \"Numéro de série\" n'est pas rempli.</strong><br />\n";
		elseif(!is_numeric($numSerie))
			echo "\t  <strong class=\"error\">Le champ \"Numéro de série\" n'est pas valide.</strong><br />\n";
		if($pageDeb == '')
			echo "\t  <strong class=\"error\">Le champ \"Page de début\" n'est pas rempli.</strong><br />\n";
		elseif(!is_numeric($pageDeb))
			echo "\t  <strong class=\"error\">Le champ \"Page de début\" n'est pas valide.</strong><br />\n";
		if($pageFin == '')
			echo "\t  <strong class=\"error\">Le champ \"Page de fin\" n'est pas rempli.</strong><br />\n";
		elseif(!is_numeric($pageFin))
			echo "\t  <strong class=\"error\">Le champ \"Page de fin\" n'est pas valide.</strong><br />\n";
		elseif($pageDeb > $pageFin)
			echo "\t  <strong class=\"error\">La page de début doit être inférieure à la page de fin.</strong><br />\n";
		if($date == '')
			echo "\t  <strong class=\"error\">Le champ \"Date de publication\" n'est pas rempli.</strong><br />\n";
		elseif(!verifDate($date))
			echo "\t  <strong class=\"error\">Le champ \"Date de publication\" n'est pas valide.</strong><br />\n";
		if( count($numAuteurs) <= 0 )
			echo "\t  <strong class=\"error\">Vous devez avoir au moins un auteur.</strong><br />\n";
		echo "\t</p>\n";
	}
	
	// On crée la liste des revues existantes
	$req=@pg_query( $conn, "SELECT * FROM Revue ORDER BY nomRevue" );
	if(!$req)
		db_error();
	
	$liste = "\t\t    <option value=\"0\">Nouveau</option>\n";
	while( $ligne = pg_fetch_array($req) )
		$liste .= "\t\t    <option value=\"" . $ligne['idrevue'] . '">' . $ligne['nomrevue'] . "</option>\n";
		
	// On crée la liste des auteurs
	$req=@pg_query( $conn, "SELECT * FROM Auteur ORDER BY nomAuteur,initialesPrenoms" );
	if(!$req)
		db_error();
	
	$liste2 = "\t\t    <option value=\"0\">Nouveau</option>\n";
	while( $ligne = pg_fetch_array($req) )
		$liste2 .= "\t\t    <option value=\"" . $ligne['idauteur'] . '">' . $ligne['initialesprenoms'] . '. ' . $ligne['nomauteur'] . "</option>\n";
	disconnect();
?>

	<form method="post" action="<?php echo $self . $urladd; ?>">
	  <table class="center">
	    <tbody>
	      <tr>
		<th>Titre de l'article&nbsp;:</th>
		<td><input type="text" name="titre" value="<?php echo $titre; ?>" /> <em>obligatoire</em></td>
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
	echo "\t\t  <em>Aucun</em>\n";
?>
		</td>
	      </tr>
	      <tr>
		<th>Ajouter un auteur&nbsp;:</th>
		<td>
		  <select name='numNouvAuteur' size='1'>
<?php echo "$liste2"; ?>
		  </select>
		  Nom&nbsp;: <input type="text" name="nomNouvAuteur" value="<? echo @$_POST['nomNouvAuteur']; ?>" />
		</td>
	      </tr>
	      <tr>
		<td></td>
		<td>
		  Initiales du prénom&nbsp;: <input type="text" name="iniNouvAuteur" value="<? echo @$_POST['iniNouvAuteur']; ?>" />
		  <input type="submit" name="AjouteAuteur" value="Ajouter"/>
		</td>
	      </tr>
	      <tr>
		<td colspan="2">&nbsp;</td>
	      </tr>
	      <tr>
		<th>Nom de la revue&nbsp;:</th>
		<td>
		  <select name="numRevue" size="1">
<?php echo "$liste"; ?>
		  </select>
<!-- A afficher que si la liste est à 0 -->
		  <input type="text" name="nomRevue" value="<? echo "$nomRevue"; ?>" />
		  <em>obligatoire</em>
		</td>
	      </tr>
	      <tr>
		<th>Numéro de volume&nbsp;:</th>
		<td><input type="text" name="numVolume" value="<? echo "$numVolume"; ?>" />
		<em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Numéro de série&nbsp;:</th>
		<td><input type="text" name="numSerie" value="<? echo "$numSerie"; ?>" />
		<em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Page de début&nbsp;:</th>
		<td><input type="text" name="pageDeb" value="<? echo "$pageDeb"; ?>" />
		<em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Page de fin&nbsp;:</th>
		<td><input type="text" name="pageFin" value="<? echo "$pageFin"; ?>" />
		<em>obligatoire</em></td>
	      </tr>
	      <tr>
		<th>Date de publication&nbsp;:</th>
		<td><input type="text" name="date" maxlength="10" value="<? echo "$date"; ?>" />
		<em>obligatoire (JJ/MM/AAAA)</em></td>
	      </tr>
	      <tr>
		<th>Mot(s) clé(s)&nbsp;:</th>
		<td><input type="text" name="motCle" value="<? echo "$motCle"; ?>" />
		<em>séparez par des virgules</em></td>
	      </tr>
	    </tbody>
	  </table>

	  <p><input type="submit" name="submit" value="Ajouter l'article" /></p>
	</form>
<?php
}

require('bottom.inc.php');

?>
