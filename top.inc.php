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


if ($NOAUTH)
    $level = 2;
else {
    if (isset($level)) {
	session_start();
	$_SESSION['level'] = $level;
    } else {
	if (isset($_GET['PHPSESSID']))
	    session_start();
	$level = isset($_SESSION['level']) ? (integer) $_SESSION['level'] :
		 (isset($db_configured) ? 0 : 2); // 2 si non configuré
    }
}

$sid = defined('SID') ? strip_tags(SID) : '';
if (!ini_get('session.use_trans_sid') &&
    !empty($sid) && isset($_SESSION['level'])) {
    $urladd = '?' . $sid;
    $urladd2 = $urladd . '&amp;';
} else {
    $urladd = '';
    $urladd2 = '?';
}

$good = array('Gecko', 'Konqueror', 'Opera', 'W3C_Validator');
if (!empty($_SERVER['HTTP_USER_AGENT']))
    foreach ($good as $agent)
	if (strpos($_SERVER['HTTP_USER_AGENT'], $agent) !== FALSE)
	    $ctype = 'application/xhtml+xml';
if (empty($ctype))
    $ctype = 'text/html';
unset($good, $agent);

header('Content-Type: ' . $ctype . '; charset=UTF-8');
header('Content-Language: fr');
if (count($_POST) > 0 || count($_GET) > 0) {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
}
if (isset($do_redir)) {
    header('Location: ' . $do_redir . $urladd .
	   (empty($sid) ? '' : '?' . $sid));
}

if ($ctype === 'application/xhtml+xml')
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

/*
 * Variables disponibles :
 *     - $self :    script en cours
 *     - $ctype :   HTTP Content-Type
 *     - $level :   niveau d'admin (0 = lire, 1 = modifier, 2 = installer)
 *     - $urladd :  chaîne à ajouter après chaque URL
 *     - $urladd2 : idem, si autres paramètres
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	       "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" dir="ltr">
  <!-- En-tête -->
  <head>
    <!-- Titre -->
    <title>iBiblio</title>

    <!-- Métadonnées -->
    <meta http-equiv="Content-Type"
	  content="<?php echo $ctype; ?>; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="fr" />
    <meta name="Author" content="Benjamin Gaillard &amp; Nicolas Riegel" />

    <!-- Styles -->
    <style type="text/css" media="screen" title="Par défaut (flexible)">
      @import "style/flexible.css";
    </style>
    <style type="text/css" media="screen" title="Sans style"></style>
  </head>

  <!-- Corps du document -->
  <body>
    <!-- Barre de titre -->
    <div id="title">
      <h1><span><abbr title="Internet Bibliography">iBiblio</abbr></span></h1>
    </div>

    <!-- Menu latéral -->
    <div id="menu">
      <!-- Titre du menu -->
      <div id="menu-top">
	<h2><span><strong>iBiblio</strong>&nbsp;» menu</span></h2>
      </div>

      <!-- Éléments du menu -->
      <div id="menu-middle">
	<ul>
<?php

$pages = array(
    'index'         => array('Accueil',                       0, TRUE ),
    'addbook'       => array('Ajout d\'un livre',             1, FALSE),
    'addarticle'    => array('Ajout d\'un article',           1, FALSE),
    'searchbook'    => array('Rechercher un livre',           0, FALSE),
    'searcharticle' => array('Rechercher un article',         0, FALSE),
    'searchkeyword' => array('Thésaurus',                     0, FALSE),
    'adduser'       => array('Ajout d\'un utilisateur',       2, FALSE),
    'remuser'       => array('Suppression d\'un utilisateur', 2, FALSE),
    'install'       => array('Installation',                  2, TRUE ),
    'uninstall'     => array('Désinstallation',               2, FALSE)
);
$first = TRUE;
foreach ($pages as $file => $prop) {
    list($name, $reqlvl, $init) = $prop;

    if ((isset($db_configured) || $init) && $reqlvl <= $level) {
	echo "\t  ";
	if ($first)
	    $first = FALSE;
	else
	    echo "-->";

	echo '<li>';
	$selected = $file === basename($self, '.php');
	if ($selected)
	    echo '<strong>';
	echo '<a href="' . $file . '.php' . $urladd . '">' . $name . '</a>';
	if ($selected)
	    echo '</strong>';
	echo "</li><!-- IE…\n";
    }
}
unset($pages, $first, $file, $name);

?>
	--></ul>

	<?php
if (isset($db_configured)) {
    if ($level === 0) {
	$page = 'admin';
	$name = 'Accès administrateur';
    } else {
	$page = 'logout';
	$name = 'Déconnexion';
    }

    $selected = basename($self, '.php') === $page;
    if ($selected)
	echo '<strong>';
    echo '<a href="' . $page . '.php' . $urladd . '">' . $name . '</a>';
    if ($selected)
	echo '</strong>';
}
?>

      </div>

      <!-- Logo du bas du menu -->
      <div id="menu-bottom">
        <div id="menu-ulp">
	  <span><a title="Université Louis Pasteur"
	  href="http://www-info.u-strasbg.fr/"><acronym
	  title="Université Louis Pasteur">ULP</acronym></a></span>
        </div>
      </div>
    </div>

    <!-- Contenu de la page -->
    <div id="contents">
      <!-- Utilisé par les styles -->
      <div class="top"><span><span></span></span></div>

      <!-- Contenu de ce cadre -->
      <div class="content">
