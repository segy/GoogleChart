GoogleChart helper for easy generating image charts using Google Chart API

Description later... see examples. I developed it for the purposes I needed now (bar and pie charts), so you may find many options missing. I will add them if I will need them in next project so please be patient or fork and contribute. Enjoy.

This plugin is for CakePHP 2.x

Short example: 

Controller:

public $helpers = array('GoogleChart.GoogleChart');

View:

// example of bar chart
echo $this->GoogleChart->create()
	->setType('bar', array('horizontal', 'grouped'))
	->setSize(500, 400)
	->setMargins(5, 5, 5, 5)
	->addData(array(1200.48, 432.3, 647.21, 635.2))
	->addMarker('value', array('format' => 'f1', 'placement' => 'c'))
	->addData(array(20, 42.3, 65.21, 95.2))
	->addMarker('value', array('size' => 14, 'color' => '000000'))
	->addAxis('x', array('labels' => array('jan 2012', 'feb 2012')))
	->addAxis('y', array('axis_or_tick' => 'l', 'size' => 12));
	
// example of pie chart
echo $this->GoogleChart->create()
	->setTitle('CHART TITLE', array('size' => 14, 'color' => '000000'))
	->setType('pie', array('3d'))
	->setSize(600, 300)
	->setMargins(10, 10, 10, 10)
	->addData(array(20, 35, 50, 10))
	->setPieChartLabels(array('first', 'second', 'third', 'and so on...'));