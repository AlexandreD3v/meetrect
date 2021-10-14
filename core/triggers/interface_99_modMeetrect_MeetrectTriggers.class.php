<?php
/* Copyright (C) 2021 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modMeetrect_MeetrectTriggers.class.php
 * \ingroup meetrect
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modMeetrect_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/meetrect/class/entryurl.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/meetrect/class/destinyurl.class.php';


/**
 *  Class of triggers for Meetrect module
 */
class InterfaceMeetrectTriggers extends DolibarrTriggers
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Meetrect triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'meetrect@meetrect';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->meetrect->enabled)) return 0; // If module is not enabled, we do nothing

			global $db;

		switch ($action) {

			case 'ROOMS_CREATE':
			$objdestiny = new DestinyURL($db);
			$objentry = new EntryURL($db);
				// Busca se já existe sala com referencia às salas de entrada e destino selecionadas
				$sql1 = "SELECT r.rowid FROM " . MAIN_DB_PREFIX . $object->table_element
				. " as r WHERE r.destiny_url = " . $object->destiny_url ." && r.entry_url = " . $object->entry_url;

				// Executa a query no DB
				$resql1 = $this->db->query($sql1);

				// Obtem o objeto  
				if ($resql1) $fetched1 = $this->db->num_rows($resql);

				print '
				console.log('.$sql1.');
				console.log('.print_r($fetched1,1).');
				';

				if($fetched1<1){	

				// Busca o status da sala de destino selecionado
				$sql = "SELECT d.status FROM " . MAIN_DB_PREFIX . $objdestiny->table_element
				.= " as d WHERE d.rowid = " . $object->destiny_url;

				// Executa a query no DB
				$resql = $this->db->query($sql);

				// Obtem o objeto
				$fetched = $this->db->fetch_array($resql);

				// Atribui o status da sala de destino à sala a ser criada
				$object->status = $fetched[0];

				return 1;
			}
			else{
				return -1;
			}

			break;

			case 'ROOMS_MODIFY':

			break;

			case 'ENTRYURL_CREATE':

				// Verifica se existe um destino para essa entrada
					$sql = "SELECT t.status FROM " . MAIN_DB_PREFIX . $object->table_element
					.= " as t WHERE t.rowid = " . $object->destiny_url;

					// Executa a query no DB
					$resql = $this->db->query($sql);

					// Obtem o objeto
					$fetched = $this->db->fetch_array($resql);

					// Se a soma das parcelas for maior que o valor do contrato,
					// o sistema impede o processo e exibe um alert por JS
					//if ($valorsomaparcelas > $valorcontratoprincipal) {
					
					
					//}

			break;

			case 'ENTRYURL_MODIFY':

			break;

			case 'DESTINYURL_CREATE':

			break;

			case 'DESTINYURL_MODIFY':
				
			break;
			
			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}
}
