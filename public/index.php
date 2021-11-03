<?php
/* Copyright (C) - 2013-2016	Jean-François FERRY    <hello@librethic.io>
 * Copyright (C) - 2019     	Laurent Destailleur    <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/custom/meetrect/public/index.php
 *       \ingroup    Meetrect
 *       \brief      Public page to access a virtual room
 */

if (!defined('NOCSRFCHECK'))   define('NOCSRFCHECK', '1');
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');
if (!defined("NOLOGIN"))       define("NOLOGIN", '1'); // If this page is public (can be called outside logged session)
if (!defined('NOIPCHECK'))		define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// TODO This should be useless. Because entity must be retreive from object ref and not from url.
$entity = (!empty($_GET['entity']) ? (int) $_GET['entity'] : (!empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) define("DOLENTITY", $entity);

require '../../../main.inc.php';


require_once DOL_DOCUMENT_ROOT.'/ticket/class/actions_ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ticket.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/core/lib/security.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';

require_once DOL_DOCUMENT_ROOT.'/custom/meetrect/class/rooms.class.php';


// Load translation files required by the page
$langs->loadLangs(array('companies', 'other', 'ticket', 'errors'));

// Get parameters
$track_id = GETPOST('track_id', 'alpha');
$action = GETPOST('action', 'alpha');
$roomid = GETPOST('room', 'alpha');

$room = new Rooms($db);

// Select
// --------------------------------------------------------------------
$sql = "SELECT r.label, r.entry_url, r.destiny_url, r.description, r.start_time FROM ".MAIN_DB_PREFIX.$room->table_element." as r
WHERE r.entry_url LIKE '%".$roomid."%' AND r.status = 1";

$resql = $db->query($sql);

$resposta = $db->fetch_object($resql);

$minutos  = ( $resposta->start_time / 60 ) % 60;     // 60000   = 60 * 1000
$horas    = $resposta->start_time / 3600 % 12;            // 3600000 = 60 * 60 * 1000

if(!strpos($resposta->destiny_url, "http")){
    $strhref = "http://".$resposta->destiny_url;
}
else{
    $strhref = $resposta->destiny_url;    
}

/*
 * View
 */

if (empty($conf->global->MEETRECT_ENABLE_PUBLIC_INTERFACE))
{
	print $langs->trans('MeetrectPublicInterfaceForbidden');
	exit;
}

$arrayofjs = array();
//$arrayofcss = array('/ticket/css/styles.css.php');
$arrayofcss = array('/ticket/css/styles.css.php');

llxHeaderTicket("Meetrect", "", 0, 0, $arrayofjs, $arrayofcss);

print '<div class="ticketpublicarea">';
print '<p style="text-align: center">Meetrect - gerenciador de salas virtuais</p>';
print '<div class="ticketform">';

if($resposta){
    print '<p style="text-align: center">'.$resposta->label.' - Início: '.$horas.':'.$minutos.'</p>
    <p style="text-align: center">'.$resposta->description.'</p>';
    print '<a href="'.$strhref.'" class="butAction marginbottomonly"><br> Acessar '.$strhref.'</div></a>';
}
else{
    print '<p style="text-align: center">Nenhuma sala informada!</p>';
}

print '<div style="clear:both;"></div>';
print '</div>';
print '</div>';

// End of page
htmlPrintOnlinePaymentFooter($mysoc, $langs, 0, $suffix, $object);

llxFooter('', 'public');

$db->close();
