<?php

class DBP_Table extends ViewableData {
	
	protected $Name;
	protected $record;
	
	function __construct($name, $record = null) {
		parent::__construct();
		$this->Name = $name;
		$this->record = $record;
	}
	
	function Database() {
		return DB::getConn()->currentDatabase();
	}
	
	function Selected() {
		return Controller::curr()->urlParams['ID'] == $this->Name;
	}
	
	function Fields() {

		$fields = new DataObjectSet();

		foreach(DB::fieldList($this->Name) as $name => $spec) {
			$v = null;
			if($this->record) {
				$d = $this->record->Data();
				$v = $d[$name];
			}
			$fields->push(new DBP_Field($this, $name, $v));
		}
		
		return $fields;
	}

	function requestVar($key) {
		$vars = Controller::curr()->getRequest()->requestVars();
		return @$vars[$key];
	}

	function Rows() {

		$vars = Controller::curr()->getRequest()->requestVars();
		
		$start = (Int)@$vars['start'];

		$rows = new DataObjectSet();
		$order = isset($vars['orderby']) && $vars['orderby'] ? "\"{$vars['orderby']}\" " . $vars['orderdir'] : '';
		$result = DB::query(DBP::select('*', $this->Name, null, $order, DBP::$records_per_page, $start));
		foreach($result as $key => $record) {
			$row = new DataObjectSet();
			foreach($record as $key => $cell) {
				if($key == 'rowNo_hide') continue;
				if(empty($field[$key])) $field[$key] = new DBP_Field($this, $key);

				$cell = strlen($cell) > DBP::$truncate_text_longer ? htmlentities(substr($cell, 0, DBP::$truncate_text_longer)) . '<div class="truncated" />' : htmlentities($cell);
				$row->push(new ArrayData(array(
					'Val' => $cell,
					'Type' => $field[$key]->type(),
					'Context' => $this->Name . '.' . $key . '.' . $record['ID'],
				)));
			}
			$rows->push(new ArrayData(array('Cells' => $row)));
		}
		
		return $rows;
	}

	function Stats() {

		$start = (Int)$this->requestVar('start');
		$total = DB::query(DBP::select('COUNT(*)', $this->Name))->value();
		$end = $start + DBP::$records_per_page - 1 > $total - 1 ? $total - 1 : $start + DBP::$records_per_page - 1;
		$stats = array(
			'total' => $total, 
			'start' => $start, 
			'length' => DBP::$records_per_page,
			'end' => $end,
			'orderlink' => 'orderby=' . $this->requestVar('orderby') . '&orderdir=' . $this->requestVar('orderdir'),
		);
		if($start > 0) { $stats['firstlink'] = 'start=0'; $stats['prevlink'] = 'start=' . ($start - DBP::$records_per_page); }
		if(isset($stats['prevlink']) && $stats['prevlink'] < 0) $stats['prevlink'] = 'start=0'; 
		if($start + DBP::$records_per_page < $stats['total']) { $stats['nextlink'] = 'start=' . ($start + DBP::$records_per_page); $stats['lastlink'] = 'start=' . (floor(($stats['total'] - 1) / DBP::$records_per_page) * DBP::$records_per_page); }
		return new ArrayData($stats);
	}

	function Link() {
		return Controller::curr()->Link() . 'show/' . $this->Name;
	}
}

class DBP_Table_Controller extends Controller {

	static $url_rule = '/$Model/$Action/$ID';
	
	function index($req) {
		aDebug('yay!'); die();
	}
}