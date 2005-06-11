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


function microtime_float()
{
   list($usec, $sec) = explode(' ', microtime());
   return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();


// TEMP
$NOAUTH = FALSE;
//$NOAUTH = TRUE;

require('connection.conf.php');

$self = basename($_SERVER['PHP_SELF']);

// Paramétrage de PHP
ini_set('session.use_cookies', FALSE);
ini_set('session.use_trans_sid', TRUE);
ini_set('arg_separator.output', '&amp;');
ini_set('url_rewriter.tags', 'a=href,form=action');

// Requiert la possession de certains droits
function require_rights($lev)
{
    global $level, $self, $urladd2;

    if ($level < $lev) {
	echo "\t" . '<p>Vous ne disposez pas des droits nécessaires pour ' .
	     'effectuer cette opération.</p>' . "\n" .
	     "\t" . '<p>Pour les obtenir, veuillez vous <a href="admin.php' .
	     $urladd2 . 'redir=' . basename($self, '.php') .
	     '">authentifier en tant qu\'administrateur</a>.</p>' . "\n";
	require('bottom.inc.php');
	exit();
    }
}

// Affiche un message d'erreur et termine l'exécution du script
function fatal_error($msg)
{
    echo "\t" . '<p><strong class="error">' . $msg . '</strong></p>' . "\n";
    require('bottom.inc.php');
    exit();
}

// Affichage un message d'erreur relative à la base de données
function db_error()
{
    fatal_error('Erreur de base de données. ' .
		'L\'avez-vous correctement installée&nbsp;?');
}

// Fonctions pour la connexion
function connect($die = TRUE)
{
    global $db_configured, $db_host, $db_name, $db_user, $db_password;

    $conn = NULL;

    if (!isset($db_configured))
	require('connection.conf.php');

    if (isset($db_configured) &&
	!empty($db_name) && !empty($db_user) && !empty($db_password))
	$conn = @pg_connect((empty($db_host) ? '' : 'host=' . $db_host) .
			    ' dbname=' . $db_name .
			    ' user=' . $db_user .
			    ' password=' . $db_password);
    if ($conn)
	return $conn;

    if ($die)
	fatal_error('Erreur de connexion à la base de données. ' .
		    'L\'avez-vous bien installée&nbsp;?');
}

// Fonction pour se déconnecter
function disconnect($conn = NULL)
{
    return @pg_close($conn);
}

?>
