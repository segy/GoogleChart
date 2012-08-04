<?php
App::uses('AppHelper', 'Helper');

/**
 * Helper for working with Google chart API
 */

class GoogleChartHelper extends AppHelper {
	/**
	 * Google chart API url
	 */
	const CHART_API_URL = 'http://%d.chart.apis.google.com/chart?';
	/**
	 * Helpers
	 */
	public $helpers = array('Html');
	/**
	 * Chart params
	 */
	protected $_params = array();
	/**
	 * Chart data
	 */
	protected $_data = array();
	/**
	 * Chart data colors
	 */
	protected $_dataColors = array();
	/**
	 * Internal counter for data sets
	 */
	protected $_ctrD = 0;
	/**
	 * Internal counter for axes
	 */
	protected $_ctrA = 0;
	/**
	 * Internal counter for performance optimization
	 */
	protected $_ctrC = 0;
	/**
	 * Colors that are used for data series
	 */
	protected $_baseColors = array('3266cc', 'c5d8ff', '76a4fa', '3398cc', '7778cb');
	
	/**
	 * Constructor
	 */
	public function __construct(View $view, $settings = array()) {
        parent::__construct($view, $settings);
		// can set base colors
		if (array_key_exists('colors', $settings))
			$this->_baseColors = $settings['colors'];
    }
	
	/**
	 * Start new chart
	 */
	public function create() {
		$this->_params = array(
			'chbh' => 'a',
			'chds' => 'a',
			'chs' => '300x300',
			'chf' => 'bg,s,00000000',
		);
		$this->_ctrD = 0;
		$this->_ctrA = 0;
		$this->_data = array();
		$this->_dataColors = array();
		return $this;
	}
	
	/**
	 * Output chart using echo
	 */
	public function __toString() {
		// base url
		$url = sprintf(self::CHART_API_URL, $this->_ctrC++);
		$this->_ctrC %= 10;
		
		// add params
		foreach ($this->_params as $key => $val)
			$url .= $key.'='.urlencode($val).'&amp;';
		// add data
		$data = $this->_data;
		array_walk($data, create_function('&$v, $k', '$v = implode(",", $v);'));
		$url .= 'chd=t:'.implode('|', $data).'&amp;';
		// add data colors
		$url .= 'chco='.implode(',', $this->_dataColors).'&amp;';
		
		// image params
		$tmp = explode('x', $this->_params['chs']);
		if (array_key_exists('imgs', $this->_params))
			$tmp = explode('x', $this->_params['imgs']);
		$im = array('width' => $tmp[0], 'height' => $tmp[1]);
		if (array_key_exists('chtt', $this->_params))
			$im['alt'] = $this->_params['chtt'];
		
		return $this->Html->image($url, $im);
	}
	
	/**
	 * Set type
	 * Possible types: line, bar, pie
	 * Possible params:
	 * 	line: xy
	 * 	bar: horizontal/vertical, grouped/stacked
	 * 	pie: 2d/3d/concentric
	 */
	public function setType($type, $params = array()) {
		switch ($type) {
			case 'line':
				$this->_params['cht'] = in_array('xy', $params) ? 'lxy' : 'lc';
				break;
			case 'bar':
				$this->_params['cht'] = 'b'.(in_array('horizontal', $params) ? 'h' : 'v').(in_array('grouped', $params) ? 'g' : 's');
				break;
			case 'pie':
				$this->_params['cht'] = 'p'.(in_array('3d', $params) ? '3' : (in_array('concentric', $params) ? 'c' : ''));
				break;
		}
		return $this;
	}
	
	/**
	 * Set title
	 * Possible params: color, size
	 */
	public function setTitle($text, $params = array()) {
		$this->_params['chtt'] = $text;
		
		// default values
		if (array_key_exists('color', $params) || array_key_exists('size', $params))
			$this->_params['chts'] = $this->_color(@$params['color']).','.$this->_float(@$params['size'] ? $params['size'] : 11.5);
		
		return $this;
	}
	
	/**
	 * Set size
	 */
	public function setSize($width, $height) {
		$this->_params['chs'] = $this->_int($width).'x'.$this->_int($height);
		return $this;
	}

	/**
	 * Set container size
	 */
	public function setContainerSize($width, $height) {
		$this->_params['imgs'] = $this->_int($width).'x'.$this->_int($height);
		return $this;
	}

	/**
	 * Set margins
	 */
	public function setMargins($top, $right, $bottom, $left) {
		$this->_params['chma'] = $this->_int($left).','.$this->_int($right).','.$this->_int($top).','.$this->_int($bottom);
		return $this;
	}
	
	/**
	 * Set pie chart labels (pie chart only)
	 */
	public function setPieChartLabels($values) {
		$this->_params['chl'] = implode('|', $values);
		return $this;
	}
	
	/**
	 * Set chart background color
	 */
	public function setBackgroundColor($color = '000000', $opacity = '00') {
		$this->_params['chf'] = 'bg,s,'.$this->_color($color).$opacity;
		return $this;
	}
	
	/**
	 * Add data set
	 * Possible params:
	 * 	color (takes color from baseColors if not set)
	 * 	marker - 
	 */
	public function addData($data, $params = array()) {
		if (count($this->_data))
			$this->_ctrD++;
		
		// color for data series
		$this->_dataColors[] = $this->_color(array_key_exists('color', $params) ? $params['color'] : $this->_baseColors[$this->_ctrD % count($this->_baseColors)]);
		
		if (!is_array($data))
			$data = array($data);
		
		$this->_data[] = $data;
		return $this;
	}
	
	/**
	 * Add marker
	 * For data marker adds marker to last added data set
	 * Possible types:
	 * 	flag, text, annotation, value (see https://developers.google.com/chart/image/docs/chart_params#gcharts_data_point_labels)
	 * 	TODO: a, c, C, d, E, h, H, o, s, v, V, x (see https://developers.google.com/chart/image/docs/chart_params#gcharts_shape_markers)
	 * 	TODO: range (see https://developers.google.com/chart/image/docs/chart_params#gcharts_range_markers)
	 * 	TODO: line (see https://developers.google.com/chart/image/docs/chart_params#gcharts_line_markers)	
	 * 	TODO: line-fill (see https://developers.google.com/chart/image/docs/chart_params#gcharts_line_fills)
	 * Possible params:
	 * 	color - text and ticks color
	 * 	size - font size
	 * 	format - for value marker (f/p/e/cCUR - see https://developers.google.com/chart/image/docs/chart_params#gcharts_data_point_labels)
	 * 	which_points - which points to draw markers on
	 * 	z_order - between -1.0 and 1.0
	 * 	placement - where to put markers (see https://developers.google.com/chart/image/docs/chart_params#gcharts_data_point_labels)
	 */
	public function addMarker($type, $params = array()) {
		$str = '';
		// text and data value
		if (in_array($type, array('flag', 'text', 'annotation', 'value'))) {
			$types = array('flag' => 'f', 'text' => 't', 'annotation' => 'A', 'value' => 'N');
			$format = $types[$type];
			// can use format for value marker
			if (array_key_exists('format', $params))
				$format .= '*'.$params['format'].'*';
			// format, color, data index, which_points, size, z_order, placement
			$str = $format.','.$this->_color(@$params['color']).','.$this->_ctrD.','.(@$params['which_points']).','.$this->_float(@$params['size'] ? $params['size'] : 11.5).','.$this->_float(@$params['z_order']).(@$params['placement'] ? ','.@$params['placement'] : '');
		}
		
		if (array_key_exists('chm', $this->_params))
			$this->_params['chm'] .= '|'.$str;
		else
			$this->_params['chm'] = $str;
			
		return $this;
	}
	
	/**
	 * Add axis
	 * Possible positions: x (bottom), y (left), t (top), r (right)
	 * Possible params:
	 * 	labels - for setting custom axis values
	 * 	label_positions - for setting spacing between labels
	 * 	color - text and ticks color
	 * 	size - font size
	 * 	alignment - between -1 and 1
	 * 	axis_or_tick - whether to show axis line and tick marks (l, t, lt, _)
	 * 	tick_lengths - tick length / array of tick lengths
	 */
	public function addAxis($position, $params = array()) {
		if (array_key_exists('chxt', $this->_params)) {
			$this->_params['chxt'] .= ','.$position;
			$this->_ctrA++;
		}
		else
			$this->_params['chxt'] = $position;
		
		if (array_key_exists('labels', $params)) {
			$str = $this->_ctrA.':|'.implode('|', $params['labels']);
			if (array_key_exists('chxl', $this->_params))
				$this->_params['chxl'] .= '|'.$str;
			else
				$this->_params['chxl'] = $str;
		}
		if (array_key_exists('label_positions', $params)) {
			$str = $this->_ctrA.','.implode(',', $params['label_positions']);
			if (array_key_exists('chxp', $this->_params))
				$this->_params['chxp'] .= '|'.$str;
			else
				$this->_params['chxp'] = $str;
		}
		if (array_key_exists('color', $params) || array_key_exists('size', $params) || array_key_exists('alignment', $params) || array_key_exists('axis_or_tick', $params)) {
			// index, color, size, alignment, axis/tick, color
			$str = $this->_ctrA.','.$this->_color(@$params['color']).','.$this->_float(@$params['size'] ? $params['size'] : 11.5).','.$this->_float(@$params['alignment']).','.(@$params['axis_or_tick'] ? $params['axis_or_tick'] : 'lt').','.$this->_color(@$params['color']);
			if (array_key_exists('chxs', $this->_params))
				$this->_params['chxs'] .= '|'.$str;
			else
				$this->_params['chxs'] = $str;
		}
		if (array_key_exists('tick_lengths', $params)) {
			$str = $this->_ctrA.','.(is_array($params['tick_lengths']) ? implode(',', $params['tick_lengths']) : $params['tick_lengths']);
			if (array_key_exists('chxtc', $this->_params))
				$this->_params['chxtc'] .= '|'.$str;
			else
				$this->_params['chxtc'] = $str;
		}
		
		return $this;
	}
	
	/**
	 * Params formatting functions
	 */
	protected function _color($color) {
		$color = preg_replace('/[^0-9A-Fa-f]/', '', $color);
		if (strlen($color) == 6)
			return $color;
		elseif (!strlen($color))
			return '676767';
		elseif (strlen($color) < 6)
			return str_pad($color, 6, $color);
		else
			return substr($color, 0, 6);
	}
	
	protected function _int($num) {
		return (int)$this->_float($num);
	}
	
	protected function _float($num) {
		return (float)preg_replace('/[^0-9.-]/', '', str_replace(',', '.', $num));
	}
}
