<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


class CSvgGraphBar extends CSvgGroup {

	protected $path;
	protected $itemid;
	protected $item_name;
	protected $options;
	protected $canvas_height;

	public function __construct($path, $metric, $canvas_height) {
		parent::__construct();

		$this->path = $path ? : [];
		$this->itemid = $metric['itemid'];
		$this->item_name = $metric['name'];
		$this->options = $metric['options'] + [
			'color' => CSvgGraph::SVG_GRAPH_DEFAULT_COLOR,
			'pointsize' => CSvgGraph::SVG_GRAPH_DEFAULT_POINTSIZE,
			'transparency' => CSvgGraph::SVG_GRAPH_DEFAULT_TRANSPARENCY,
			'width' => CSvgGraph::SVG_GRAPH_DEFAULT_LINE_WIDTH,
			'order' => 1
		];
		$this->canvas_height = $canvas_height + 10;
	}

	public function makeStyles() {
		$this
			->addClass(CSvgTag::ZBX_STYLE_GRAPH_BAR)
			->addClass(CSvgTag::ZBX_STYLE_GRAPH_BAR.'-'.$this->itemid.'-'.$this->options['order']);

		return [
			'.'.CSvgTag::ZBX_STYLE_GRAPH_BAR.'-'.$this->itemid.'-'.$this->options['order'] => [
				'fill-opacity' => $this->options['transparency'] * 0.1,
				'fill' => $this->options['color']
			]
		];
	}

	protected function draw() {
		foreach ($this->path as $point) {
			$this->addItem(
				(new CSvgPolygon(
					[
						[$point[0], $this->canvas_height],
						[$point[0], $point[1]],
						[$point[0] + $point[3], $point[1]],
						[$point[0] + $point[3], $this->canvas_height]
					]
				))
					// Value.
					->setAttribute('label', $point[2])
					// Bar X.
					->setAttribute('data-cx', $point[0])
					// Bar Y.
					->setAttribute('data-cy', $point[1])
					// Bar width.
					->setAttribute('data-width', round($point[3] / 2))
					// X for tooltip.
					->setAttribute('data-px', $point[4])
			);
		}
	}

	public function toString($destroy = true) {
		$this->setAttribute('data-set', 'bar')
			->setAttribute('data-metric', CHtml::encode($this->item_name))
			->setAttribute('data-color', $this->options['color'])
			->addItem(
				(new CSvgCircle(-10, -10, $this->options['width'] + 4))
					->addClass(CSvgTag::ZBX_STYLE_GRAPH_HIGHLIGHTED_VALUE)
			)
			->draw();

		return parent::toString($destroy);
	}
}
