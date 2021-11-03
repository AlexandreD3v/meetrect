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

			case 'CHECK_ACTIVATED':

			break;

			case 'ROOMS_CREATE':
			case 'ROOMS_MODIFY':
			case 'ROOMS_VALIDATE':
				$fetched1 = 0;
				$error = 0;
				// Busca se já existe sala com referencia às urls de entrada e destino informadas
				$sql1 = "SELECT r.rowid FROM " . MAIN_DB_PREFIX . $object->table_element . " as r WHERE  r.entry_url LIKE '%" . $object->entry_url."%'";

				// Executa a query no DB
				$resql1 = $this->db->query($sql1);

				// Obtem o objeto caso exista
				if ($resql1) $hasentry = $this->db->fetch_object($resql1);
				if($hasentry){
					$sql2 = "SELECT r1.rowid, r1.ref, r1.destiny_url, r1.entry_url, r1.status FROM " . MAIN_DB_PREFIX . $object->table_element . " as r1 WHERE  r1.ref = " . $object->ref."";

					// Executa a query no DB
					$resql2 = $this->db->query($sql2);

					// Obtem o objeto caso exista
					if ($resql1) $duplicatedroom = $this->db->fetch_object($resql);
				}


				//Se houver registro
				if($duplicatedroom){
					//E se fizer referência ao mesmo registro de entrada
					if($duplicatedroom->entry_url == $object->entry_url){
						//Caso tenha o mesmo destino, se trata de uma sala duplicada e não pode ser salva
						if($duplicatedroom->destiny_url == $object->destiny_url && $duplicatedroom->ref != $object->ref){
							$error = true;
						}
						//Caso contrario, se trata de um novo destino para a mesma entrada ou do proprio registro
						else{
							if($object->status && $duplicatedroom->status){
								$sqlupdt = "UPDATE ". MAIN_DB_PREFIX . $object->table_element
											." SET status = 0 WHERE entry_url LIKE '%".$duplicatedroom->entry_url
											."%' and rowid != ". $duplicatedroom->rowid;

								$this->db->query($sqlupdt);
							}
							$error = false;
						}
					}

				}
				
				if(!$error){
					setEventMessages("Registro criado com sucesso", null);
					return 1;
				}
				else{
					setEventMessages("Sala duplicada. Altere a url de destino 
						caso queira criar um novo destino para um url de entrada",
						null, 'errors');
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

