<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Permission extends CI_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
	}

	// Use for authentication library build navigator
	function nav($extension = "")
	{
		if(!$extension) exit("Undefined extension");
		$isadmin = (int) $this->input->get("isadmin");

		$type = $this->input->get("type");
		if($type) $this->sub = strtoupper($type) . "_";
		$pipeline = array(
			array('$match' => array("extension" => $extension)),
			array('$lookup' => array(
					"from" => "{$this->sub}Role",
				    "localField" => "role_id",
				    "foreignField" => "_id",
				    "as" => "role"
				)
			),
			array('$unwind' => '$role'),
			array('$replaceRoot' => array('newRoot' => '$role')),
			array('$unwind' => '$privileges'),
			array('$group' => array(
				'_id'		=> '$privileges.module_id',
				'view'		=> array('$max' => '$privileges.view'),
				'create'	=> array('$max' => '$privileges.create'),
				'update'	=> array('$max' => '$privileges.update'),
				'delete'	=> array('$max' => '$privileges.delete')
				)
			),
			array('$lookup' => array(
					"from" => "{$this->sub}Module",
				    "localField" => "_id",
				    "foreignField" => "_id",
				    "as" => "module"
				)
			),
			array('$unwind' => '$module'),
			array('$match' => array('module.active' => true))
		);
		$resultPrivileges = $this->crud->aggregate_pipeline("{$this->sub}User", $pipeline);
		$permittedModules = [null];
		
		foreach ($resultPrivileges as $privilege) {
			if($privilege["view"])
				$permittedModules[] = new MongoDB\BSON\ObjectId($privilege["id"]);
		}
		$where = array(
			"parent_id" 	=> array('$exists' => false)
		);
		$where['$or'] = array(
        	array('module_id' => null),
        	array('module_id' => array('$in' => $permittedModules))
        );
        if($isadmin) {
        	$navData = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
				array('$match' => $where),
				array('$lookup' => array(
						"from" => "{$this->sub}Navigator",
					    "localField" => "_id",
					    "foreignField" => "parent_id",
					    "as" => "sub"
					)
				),
				array('$match' => array("visible" => true)),
				array('$sort' => array('pos' => 1)),
				array('$project' => array(
						'name'			=> 1,
						'description'	=> 1,
						'visible'		=> 1,
						'icon'			=> 1,
						'uri'			=> 1, 
						'sub' 			=> array(
							'$filter' => array(
								'input' => '$sub',
								'as'	=> "sub",
								'cond'	=> array('$eq' => array('$$sub.visible', true))
							)
						)
					)
				)
			));
        } else {
        	$where["only_admin"] = array('$ne' => TRUE);
			$navData = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
				array('$match' => $where),
				array('$lookup' => array(
						"from" => "{$this->sub}Navigator",
					    "localField" => "_id",
					    "foreignField" => "parent_id",
					    "as" => "sub"
					)
				),
				array('$match' => array("visible" => true)),
				array('$match' => array(
					'$or' => array(
						array("module_id" => array('$exists' => true)),
						array("module_id" => null, "hasChild" => false),
						array("module_id" => null, "hasChild" => true, "sub.module_id" => array('$in' => $permittedModules))
					)
				)),
				array('$sort' => array('pos' => 1)),
				array('$project' => array(
						'name'			=> 1,
						'description'	=> 1,
						'visible'		=> 1,
						'hasChild'		=> 1,
						'icon'			=> 1,
						'uri'			=> 1, 
						'sub' 			=> array(
							'$filter' => array(
								'input' => '$sub',
								'as'	=> "sub",
								'cond'	=> array('$and' => array(
									array('$eq' => array('$$sub.visible', true)),
									array('$in' => array('$$sub.module_id', $permittedModules))
								))
							)
						)
					)
				),
				array('$match' => array(
					'$or' => array(
						array('hasChild' => array('$ne' => true)),
						array('hasChild' => true, 'sub' => array('$gt' => []))
					)
				))
			));
		}
		// Sort sub navigator
		foreach ($navData as &$doc) {
			if(count($doc["sub"]) > 1) {
				usort($doc["sub"], function($a, $b) {
					$pos_a = isset($a->pos) ? $a->pos : 0;
					$pos_b = isset($b->pos) ? $b->pos : 0;
				    return $pos_a - $pos_b;
				});
			}
		}

		$lang = $this->input->get("lang");
		if($lang) {
			$this->load->model("language_model");
			$navData = $this->language_model->translate($navData, "SIDEBAR", $lang, $this->sub);
		}
		$response = $navData;

		echo json_encode($response);
	}

	// Use for authentication library
	function access($extension = "")
	{
		if(!$extension) exit("Undefined extension");
		$isadmin = (int) $this->input->get("isadmin");

		$type = $this->input->get("type");
		if(!$this->sub && $type) $this->sub = strtoupper($type) . "_";

		$pipeline = array(
			array('$match' => array("extension" => $extension)),
			array('$lookup' => array(
					"from" => "{$this->sub}Role",
				    "localField" => "role_id",
				    "foreignField" => "_id",
				    "as" => "role"
				)
			),
			array('$unwind' => '$role'),
			array('$replaceRoot' => array('newRoot' => '$role')),
			array('$unwind' => '$privileges'),
			array('$group' => array(
				'_id'		=> '$privileges.module_id',
				'view'		=> array('$max' => '$privileges.view'),
				'create'	=> array('$max' => '$privileges.create'),
				'update'	=> array('$max' => '$privileges.update'),
				'delete'	=> array('$max' => '$privileges.delete'),
				'actions'	=> array('$push'=> '$privileges.actions')
				)
			),
			array('$lookup' => array(
					"from" => "{$this->sub}Module",
				    "localField" => "_id",
				    "foreignField" => "_id",
				    "as" => "module"
				)
			),
			array('$unwind' => '$module'),
			array('$match' => array('module.active' => true)),
			array('$lookup' => array(
					"from" => "{$this->sub}Navigator",
				    "localField" => "_id",
				    "foreignField" => "module_id",
				    "as" => "navigator"
				)
			),
			array('$unwind' => '$navigator'),
			array('$match' => array(
				"navigator.uri" 		=> array('$nin' => ["parent","header"]),
				"navigator.hasChild" 	=> array('$ne' => TRUE),
			)),
			array(
				'$project' => array(
					"_id" 		=> 0,
					"name"		=> '$navigator.name',
					"visible"	=> '$navigator.visible',
					"module_id" => '$module.id',
					"uri"		=> '$navigator.uri',
					"apis"		=> '$navigator.apis',
					"view"		=> 1,
					"create"	=> 1,
					"update"	=> 1,
					"delete"	=> 1,
					"actions"	=> array('$reduce' => array(
			            "input"	=> '$actions',
			            "initialValue"	=> array(),
			            "in"	=> array('$setUnion' => array('$$value', '$$this'))
			        ))
				)
			)
		);
		$resultPrivileges = $this->crud->aggregate_pipeline("{$this->sub}User", $pipeline);
		// Default privileges
		$where = array("uri" => array('$nin' => ["header", "parent"]));
		if(!$isadmin) $where["only_admin"] = array('$ne' => TRUE);

        $where['$or'] = array(
        	array('module_id' => array('$exists' => false)),
        	array('module_id' => null)
        );
		$resultNavigators = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
			array('$match' => $where),
			array('$project' => array(
				"_id" 		=> 0,
				"name"		=> 1,
				"visible"	=> 1,
				"uri" 		=> 1,
				"apis" 		=> 1,
				"view" 		=> array('$literal' => true),
				"create" 	=> array('$literal' => true),
				"update" 	=> array('$literal' => true),
				"delete" 	=> array('$literal' => true)
			))
		));
		$response = array_merge($resultPrivileges, $resultNavigators);
		echo json_encode($response);
	}

	// Use for user page
	function access_from_role_id($id = "")
	{
		$this->sub = set_sub_collection("");
		if($id) {
			$pipeline = array(
				array('$match' => array("_id" => new MongoDB\BSON\ObjectId($id))),
				array('$unwind' => '$privileges'),
				array('$group' => array(
					'_id'		=> '$privileges.module_id',
					'view'		=> array('$max' => '$privileges.view'),
					'create'	=> array('$max' => '$privileges.create'),
					'update'	=> array('$max' => '$privileges.update'),
					'delete'	=> array('$max' => '$privileges.delete'),
					'actions'	=> array('$push'=> '$privileges.actions')
					)
				),
				array('$lookup' => array(
						"from" => "{$this->sub}Module",
					    "localField" => "_id",
					    "foreignField" => "_id",
					    "as" => "module"
					)
				),
				array('$unwind' => '$module'),
				array('$match' => array('module.active' => true)),
				array('$lookup' => array(
						"from" => "{$this->sub}Navigator",
					    "localField" => "_id",
					    "foreignField" => "module_id",
					    "as" => "navigator"
					)
				),
				array('$unwind' => '$navigator'),
				array(
					'$project' => array(
						"_id" 		=> 0,
						"module_id" => '$module._id',
						"uri"		=> '$navigator.uri',
						"name"		=> '$navigator.name',
						"view"		=> 1,
						"create"	=> 1,
						"update"	=> 1,
						"delete"	=> 1,
						"actions"	=> array('$reduce' => array(
				            "input"	=> '$actions',
				            "initialValue"	=> array(),
				            "in"	=> array('$setUnion' => array('$$value', '$$this'))
				        ))
					)
				)
			);
			$resultPrivileges = $this->crud->aggregate_pipeline("{$this->sub}Role", $pipeline);
		} else $resultPrivileges = array();
		// Default privileges
		$where = array(
			"uri" 			=> array('$nin' => ["header",""]),
			"only_admin" 	=> array('$ne' => TRUE)
		);

        $where['$or'] = array(
        	array('module_id' => array('$exists' => false)),
        	array('module_id' => null)
        );
		$resultNavigators = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
			array('$match' => $where),
			array('$project' => array(
				"_id" => 0,
				"uri" => 1,
				"name"	=> 1,
				"view" => array('$literal' => true),
				"create" => array('$literal' => true),
				"update" => array('$literal' => true),
				"delete" => array('$literal' => true)
			))
		));
		$response = array_merge($resultPrivileges, $resultNavigators);

		$lang = $this->input->get("lang");
		if($lang) {
			$this->load->model("language_model");
			$response = $this->language_model->translate($response, "SIDEBAR", $lang, $this->sub);
		}
		
		echo json_encode($response);
	}

	// Use for navigator page
	function access_from_module_id()
	{
		$this->sub = set_sub_collection("");
		$collection = "User";
		$id = $this->input->get("id");
		$only_admin = $this->input->get("only_admin");
		if($only_admin) {
			$pipeline = array(
				array('$match' => array('active' => true)),
				array('$group' => array(
						'_id' => '$isadmin',
						'extensions' => array('$push' => '$extension')
					)
				),
				array('$match' => array('_id' => true)),
				array('$project' => array('_id' => 0))
			);
			$response = $this->crud->aggregate_pipeline("{$this->sub}{$collection}", $pipeline);
		} else {
			if(!$id) {
				$pipeline = array(
					array('$match' => array('active' => true)),
					array('$group' => array(
							'_id' => null,
							'extensions' => array('$push' => '$extension')
						)
					),
					array('$project' => array('_id' => 0))
				);
				$response = $this->crud->aggregate_pipeline("{$this->sub}{$collection}", $pipeline);
			} else {
				$pipeline = array(
					array('$match' => array("_id" => new MongoDB\BSON\ObjectId($id), "active" => true)),
					array('$lookup' => array(
							"from" => "{$this->sub}Role",
						    "localField" => "_id",
						    "foreignField" => "privileges.module_id",
						    "as" => "role"
						)
					),
					array('$unwind' => '$role'),
					array('$lookup' => array(
							"from" => "{$this->sub}{$collection}",
						    "localField" => "role._id",
						    "foreignField" => "role_id",
						    "as" => "users"
						)
					),
					array('$unwind' => '$users'),
					array('$replaceRoot' => array('newRoot' => '$users')),
					array('$match' => array('active' => true)),
					array(
						'$project' => array(
							"_id" => 0,
							"extension" => 1,
							"temp"		 => array('$literal' => 1)
						),
					),
					array(
						'$group' => array(
							"_id" => '$temp',
							"extensions" => array('$push' => '$extension')
						)
					),
					array(
						'$project' => array(
							"_id" => 0
						)
					)
				);
				$response = $this->crud->aggregate_pipeline("{$this->sub}Module", $pipeline);
			}
		}
		echo json_encode($response);
	}

	// Use for user page
	function nav_from_role_id($id = "")
	{
		$type = $this->input->get("type");
		if(!$this->sub && $type) $this->sub = strtoupper($type) . "_";
		$permittedModules = [];
		if($id) {
			$pipeline = array(
				array('$match' => array("_id" => new MongoDB\BSON\ObjectId($id))),
				array('$unwind' => '$privileges'),
				array('$group' => array(
					'_id'		=> '$privileges.module_id',
					'view'		=> array('$max' => '$privileges.view'),
					'create'	=> array('$max' => '$privileges.create'),
					'update'	=> array('$max' => '$privileges.update'),
					'delete'	=> array('$max' => '$privileges.delete')
					)
				),
				array('$lookup' => array(
						"from" => "{$this->sub}Module",
					    "localField" => "_id",
					    "foreignField" => "_id",
					    "as" => "module"
					)
				),
				array('$unwind' => '$module'),
				array('$match' => array('module.active' => true))
			);
			$resultPrivileges = $this->crud->aggregate_pipeline("{$this->sub}Role", $pipeline);
			foreach ($resultPrivileges as $privilege) {
				if($privilege["view"])
					$permittedModules[] = new MongoDB\BSON\ObjectId($privilege["id"]);
			}
		}
		$where = array(
			"parent_id" 	=> array('$exists' => false),
			"only_admin"	=> array('$ne' => true)
		);
		$where['$or'] = array(
        	array('module_id' => null),
        	array('module_id' => array('$in' => $permittedModules))
        );
        $navData = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
			array('$match' => $where),
			array('$lookup' => array(
					"from" => "{$this->sub}Navigator",
				    "localField" => "_id",
				    "foreignField" => "parent_id",
				    "as" => "sub"
				)
			),
			array('$match' => array("visible" => true)),
			array('$match' => array(
				'$or' => array(
					array("module_id" => array('$exists' => true)),
					array("module_id" => null, "hasChild" => false),
					array("module_id" => null, "hasChild" => true, "sub.module_id" => array('$in' => $permittedModules))
				)
			)),
			array('$sort' => array('pos' => 1)),
			array('$project' => array(
					'name'		=> 1,
					'visible'	=> 1,
					'hasChild'	=> 1,
					'icon'		=> 1,
					'uri'		=> 1, 
					'sub' 		=> array(
						'$filter' => array(
							'input' => '$sub',
							'as'	=> "sub",
							'cond'	=> array('$and' => array(
								array('$eq' => array('$$sub.visible', true)),
								array('$in' => array('$$sub.module_id', $permittedModules))
							))
						)
					)
				)
			),
			array('$match' => array(
				'$or' => array(
					array('hasChild' => array('$ne' => true)),
					array('hasChild' => true, 'sub' => array('$gt' => []))
				)
			))
		));
		// Sort sub navigator
		foreach ($navData as &$doc) {
			if(count($doc["sub"]) > 1) {
				usort($doc["sub"], function($a, $b) {
					$pos_a = isset($a->pos) ? $a->pos : 0;
					$pos_b = isset($b->pos) ? $b->pos : 0;
				    return $pos_a - $pos_b;
				});
			}
		}

		$lang = $this->input->get("lang");
		if($lang) {
			$this->load->model("language_model");
			$navData = $this->language_model->translate($navData, "SIDEBAR", $lang, $this->sub);
		}
		
		$response = $navData;
		echo json_encode($response);
	}

	function nav_from_modules()
	{
		$modules = $this->input->get("modules");
		$permittedModules = array();
		if($modules) {
			foreach ($modules as $id) {
				$permittedModules[] = new MongoDB\BSON\ObjectId($id);
			}
		}
		$type = $this->input->get("type");
		if(!$this->sub && $type) $this->sub = strtoupper($type) . "_";
		$where = array(
			"parent_id" 	=> array('$exists' => false),
			"only_admin"	=> array('$ne' => true)
		);
		$where['$or'] = array(
        	array('module_id' => null),
        	array('module_id' => array('$in' => $permittedModules))
        );
        $navData = $this->crud->aggregate_pipeline("{$this->sub}Navigator", array(
			array('$match' => $where),
			array('$lookup' => array(
					"from" => "{$this->sub}Navigator",
				    "localField" => "_id",
				    "foreignField" => "parent_id",
				    "as" => "sub"
				)
			),
			array('$match' => array("visible" => true)),
			array('$match' => array(
				'$or' => array(
					array("module_id" => array('$exists' => true)),
					array("module_id" => null, "hasChild" => false),
					array("module_id" => null, "hasChild" => true, "sub.module_id" => array('$in' => $permittedModules))
				)
			)),
			array('$sort' => array('pos' => 1)),
			array('$project' => array(
					'name'		=> 1,
					'visible'	=> 1,
					'hasChild'	=> 1,
					'icon'		=> 1,
					'uri'		=> 1, 
					'sub' 		=> array(
						'$filter' => array(
							'input' => '$sub',
							'as'	=> "sub",
							'cond'	=> array('$and' => array(
								array('$eq' => array('$$sub.visible', true)),
								array('$in' => array('$$sub.module_id', $permittedModules))
							))
						)
					)
				)
			),
			array('$match' => array(
				'$or' => array(
					array('hasChild' => array('$ne' => true)),
					array('hasChild' => true, 'sub' => array('$gt' => []))
				)
			))
		));
		// Sort sub navigator
		foreach ($navData as &$doc) {
			if(count($doc["sub"]) > 1) {
				usort($doc["sub"], function($a, $b) {
					$pos_a = isset($a->pos) ? $a->pos : 0;
					$pos_b = isset($b->pos) ? $b->pos : 0;
				    return $pos_a - $pos_b;
				});
			}
		}

		$lang = $this->input->get("lang");
		if($lang) {
			$this->load->model("language_model");
			$navData = $this->language_model->translate($navData, "SIDEBAR", $lang, $this->sub);
		}
		
		$response = $navData;
		echo json_encode($response);
	}
}