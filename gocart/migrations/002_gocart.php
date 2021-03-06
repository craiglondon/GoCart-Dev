<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_gocart extends CI_migration {
	
	public function up()
	{
		//eliminate heard_about from orders tbale
		if($this->db->field_exists('postcode_required', 'countries'))
		{
			$fields	= array('postcode_required'=>array('name'=>'zip_required'));
			$this->dbforge->modify_column('countries', $fields);
		}
		
		//if the banner_collections table does not exist, run the migration
		if (!$this->db->table_exists('banner_collections'))
		{
			//create banner collections
			$this->dbforge->add_field(array(
				'banner_collection_id'	=> array(
					'type'				=> 'INT',
					'constraint'		=> 4,
					'unsigned'			=> TRUE,
					'auto_increment'	=> TRUE
				),
				'name'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 32
				)
			));
				
			$this->dbforge->add_key('banner_collection_id', TRUE);
			$this->dbforge->create_table('banner_collections', TRUE);
		
			//create 2 collections to replace the current Banners & Boxes
			$this->db->insert('banner_collections', array('banner_collection_id'=>1, 'name'=>'Homepage Banners'));
			$this->db->insert('banner_collections', array('banner_collection_id'=>2, 'name'=>'Homepage Boxes'));
		}
		
		if ($this->db->field_exists('id', 'banners'))
		{
			//update banner table
			//individual banners
			$fields	= array(
				'id'					=> array(
					'name'				=> 'banner_id',
					'type'				=> 'INT',
					'constraint'		=> 9,
					'unsigned'			=> TRUE,
					'auto_increment'	=> TRUE
				),
				'title'					=> array(
					'name'				=> 'name'
				),
				'enable_on'				=> array(
					'name'				=> 'enable_date',
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'disable_on'			=> array(
					'name'				=> 'disable_date',
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'link'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 255
				)
			);
			$this->dbforge->modify_column('banners', $fields);
			
			//add the new column
			$fields	= array(
			'banner_collection_id'	=> array(
				'type'				=> 'INT',
				'constraint'		=> 4,
				'unsigned'			=> TRUE
				)
			);
			$this->dbforge->add_column('banners', $fields);
		
			//put them all in the homepage banners collection
			$this->db->where('id !=', 0)->update('banners', array('banner_collection_id', 1));
		}
		
		if ($this->db->table_exists('boxes'))
		{
			//move boxes over and delete the field.
			$boxes = $this->db->get('boxes')->result();
			if($boxes)
			{
				foreach($boxes as $b)
				{
					$new_box = array();
				
					$new_box['name']					= $b->title;
					$new_box['enable_date']				= $b->enable_on;
					$new_box['disable_date']			= $b->disable_on;
					$new_box['banner_collection_id']	= 2;
					$new_box['link']					= $b->link;
					$new_box['image']					= $b->image;
					$new_box['sequence']				= $b->sequence;
					$new_box['new_window']				= $b->new_window;
				
					//put the old boxes into the updated banners table with a foreign key pointing at the homepage box collection
					$this->db->insert('banners', $new_box);
				}
			}
			//drop the boxes table
			$this->dbforge->drop_table('boxes');
		}
	}
	
	public function down()
	{
		if($this->db->field_exists('zip_required', 'countries'))
		{
			$fields	= array('zip_required'=>array('name'=>'postcode_required'));
			$this->dbforge->modify_column('countries', $fields);
		}
		
		//moving down to the old banner and box system is destructive.
		if ($this->db->table_exists('banner_collections'))
		{
			//drop the boxes table
			$this->dbforge->drop_table('banner_collections');
		}
		
		if ($this->db->table_exists('banners'))
		{
			$this->dbforge->drop_table('banners');
			
			
			//create the old banners table
			//individual banners
			$this->dbforge->add_field(array(
				'id'					=> array(
					'type'				=> 'INT',
					'constraint'		=> 11,
					'unsigned'			=> TRUE,
					'auto_increment'	=> TRUE
				),
				'sequence'				=> array(
					'type'				=> 'INT',
					'constraint'		=> 11,
					'unsigned'			=> TRUE,
				),
				'title'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 128
				),
				'enable_on'			=> array(
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'disable_on'			=> array(
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'image'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 64
				),
				'link'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 255,
				),
				'new_window'			=> array(
					'type'				=> 'tinyint',
					'constraint'		=> 1,
					'default'			=> 0
				)
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('banners', TRUE);
		}
		
		if (!$this->db->table_exists('boxes'))
		{	
			//create table fox boxes
			$this->dbforge->add_field(array(
				'id'					=> array(
					'type'				=> 'INT',
					'constraint'		=> 11,
					'unsigned'			=> TRUE,
					'auto_increment'	=> TRUE
				),
				'sequence'				=> array(
					'type'				=> 'INT',
					'constraint'		=> 11,
					'unsigned'			=> TRUE,
				),
				'title'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 128
				),
				'enable_on'			=> array(
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'disable_on'			=> array(
					'type'				=> 'date',
					'null'				=> TRUE
				),
				'image'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 64
				),
				'link'					=> array(
					'type'				=> 'varchar',
					'constraint'		=> 255,
				),
				'new_window'			=> array(
					'type'				=> 'tinyint',
					'constraint'		=> 1,
					'default'			=> 0
				)
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('boxes', TRUE);
		}
		
		
	}
	
}
