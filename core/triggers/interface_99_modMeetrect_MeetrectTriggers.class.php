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
require_once DOL_DOCUMENT_ROOT.'/custom/meetrect/class/rooms.class.php';
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
			case 'ROOMS_MODIFY':
				$objdestiny = new DestinyURL($db);
				$fetched1 = 0;
				// Busca se já existe sala com referencia às salas de entrada e destino selecionadas
				$sql1 = "SELECT r.rowid FROM " . MAIN_DB_PREFIX . $object->table_element
					. " as r WHERE r.destiny_url = " . $object->destiny_url ." && r.entry_url = " . $object->entry_url;

				// Executa a query no DB
				$resql1 = $this->db->query($sql1);

				// Obtem o objeto caso exista
				if ($resql1) $duplicatedroom = $this->db->num_rows($resql);

				if($duplicatedroom<1  || $duplicatedroom->rowid == $object->rowid){

					// Busca o status da sala de destino selecionada
					$sql = "SELECT d.status FROM " . MAIN_DB_PREFIX . $objdestiny->table_element
							.= " as d WHERE d.rowid = " . $object->destiny_url;

					// Executa a query no DB
					$resql = $this->db->query($sql);

					// Obtem o objeto
					$fetched = $this->db->fetch_array($resql);

					// Atribui o status da sala de destino à sala a ser criada
					$object->status = $fetched[0];

					setEventMessages("Registro criado com sucesso", null);
					return 1;
				}
				else{
					setEventMessages("Sala duplicada", null, 'errors');
					return -1;
				}

			break;

			case 'ENTRYURL_CREATE':
			case 'ENTRYURL_MODIFY':
			$objroom = new Rooms($db);
			$objdestiny = new DestinyURL($db);
			// Verifica se existe uma entrada com a mesma url
				$sql = "SELECT e.url FROM " . MAIN_DB_PREFIX . $object->table_element
				." as e WHERE e.url LIKE '%" . $object->url . "%'";

				// Executa a query no DB
				$resql = $this->db->query($sql);

				// Obtem o objeto
				if ($resql) $duplicatedentry = $this->db->fetch_array($resql);

				if($duplicatedentry<1 || $duplicatedentry->rowid == $object->rowid){
					if($object->rowid > 0) {
						// Busca a sala, caso existir, onde a url de entrada esteja selecionada
						$sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . $objroom->table_element
								.= " as r WHERE r.entry_url = " . $object->id;

						// Executa a query no DB
						$resql1 = $this->db->query($sql1);
						// Obtem o objeto
						if (!$resql) $room_to_update = $this->db->fetch_object($resql1);

						if ($room_to_update) {
							// Busca o status da url de destino, caso existir sala criada
							$sql2 = "SELECT d.status FROM " . MAIN_DB_PREFIX . $objdestiny->table_element
									.= " as d WHERE d.rowid = " . $room_to_update->destiny_url;

							// Executa a query no DB
							$resql2 = $this->db->query($sql2);

							// Obtem o objeto
							if (!$resql2) $fetched2 = $this->db->fetch_array($resql2);

							// Atribui o status da sala de destino à sala a ser atualizada (room)
							if ($fetched2[0] && $object->status){
								$room_to_update->status = true;
								$room_to_update->update($user, true);
							}
							else {
								$room_to_update->status = false;
								$room_to_update->update($user, true);
							}
						}
					}
					setEventMessages("Registro criado com sucesso", "");
					return 1;
				}
				else{
					setEventMessages("URL duplicada", "", 'errors');
					return -1;
				}
			break;

			case 'DESTINYURL_CREATE':
			case 'DESTINYURL_MODIFY':
			$objroom = new Rooms($db);
			$objentry = new EntryURL($db);
			$iddestiny = $object->rowid;

			// Verifica se existe um destino com a mesma url
			$sql = "SELECT d.url FROM " . MAIN_DB_PREFIX . $object->table_element
					.= " as d WHERE d.url LIKE '%" . $object->url . "%'";

			// Executa a query no DB
			$resql = $this->db->query($sql);

			// Obtem o objeto
			if ($resql) $duplicated_destiny = $this->db->fetch_array($resql);

			// Se o destino não for duplicado ou se for o proprio destino
			if($duplicated_destiny<1 || $duplicated_destiny->rowid == $iddestiny) {
				setEventMessages("Registro criado com sucesso", "");
				//return 1;
			}
			else {
				setEventMessages("URL duplicada", "Modifique a url do registro", 'errors');

				return -1;
			}
			
			break;

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}
}


