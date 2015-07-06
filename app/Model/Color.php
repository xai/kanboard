<?php

namespace Model;

/**
 * Color model
 *
 * @package  model
 * @author   Frederic Guillot
 */
class Color extends Base
{
    /**
     * Default colors
     *
     * @access private
     * @var array
     */
    private $default_colors = array(
        'yellow' => array(
            'name' => 'Yellow',
            'background' => 'rgb(245, 247, 196)',
            'border' => 'rgb(223, 227, 45)',
        ),
        'blue' => array(
            'name' => 'Blue',
            'background' => 'rgb(219, 235, 255)',
            'border' => 'rgb(168, 207, 255)',
        ),
        'green' => array(
            'name' => 'Green',
            'background' => 'rgb(189, 244, 203)',
            'border' => 'rgb(74, 227, 113)',
        ),
        'purple' => array(
            'name' => 'Purple',
            'background' => 'rgb(223, 176, 255)',
            'border' => 'rgb(205, 133, 254)',
        ),
        'red' => array(
            'name' => 'Red',
            'background' => 'rgb(255, 187, 187)',
            'border' => 'rgb(255, 151, 151)',
        ),
        'orange' => array(
            'name' => 'Orange',
            'background' => 'rgb(255, 215, 179)',
            'border' => 'rgb(255, 172, 98)',
        ),
        'grey' => array(
            'name' => 'Grey',
            'background' => 'rgb(238, 238, 238)',
            'border' => 'rgb(204, 204, 204)',
        ),
        'brown' => array(
            'name' => 'Brown',
            'background' => '#d7ccc8',
            'border' => '#4e342e',
        ),
        'deep_orange' => array(
            'name' => 'Deep Orange',
            'background' => '#ffab91',
            'border' => '#e64a19',
        ),
        'dark_grey' => array(
            'name' => 'Dark Grey',
            'background' => '#cfd8dc',
            'border' => '#455a64',
        ),
        'pink' => array(
            'name' => 'Pink',
            'background' => '#f48fb1',
            'border' => '#d81b60',
        ),
        'teal' => array(
            'name' => 'Teal',
            'background' => '#80cbc4',
            'border' => '#00695c',
        ),
        'cyan' => array(
            'name' => 'Cyan',
            'background' => '#b2ebf2',
            'border' => '#00bcd4',
        ),
        'lime' => array(
            'name' => 'Lime',
            'background' => '#e6ee9c',
            'border' => '#afb42b',
        ),
        'light_green' => array(
            'name' => 'Light Green',
            'background' => '#dcedc8',
            'border' => '#689f38',
        ),
        'amber' => array(
            'name' => 'Amber',
            'background' => '#ffe082',
            'border' => '#ffa000',
        ),
    );

    /**
     * Find a color id from the name or the id
     *
     * @access public
     * @param  string  $color
     * @return string
     */
    public function find($color)
    {
        $color = strtolower($color);

        foreach ($this->default_colors as $color_id => $params) {
            if ($color_id === $color) {
                return $color_id;
            }
            else if ($color === strtolower($params['name'])) {
                return $color_id;
            }
        }

        return '';
    }

    /**
     * Get available colors
     *
     * @access public
     * @return array
     */
    public function getList($prepend = false)
    {
        $listing = $prepend ? array('' => t('All colors')) : array();

        foreach ($this->default_colors as $color_id => $color) {
            $listing[$color_id] = t($color['name']);
        }

        return $listing;
    }

    /**
     * Get the default color
     *
     * @access public
     * @return string
     */
    public function getDefaultColor()
    {
        return 'yellow'; // TODO: make this parameter configurable
    }

    /**
     * Get Bordercolor from string
     *
     * @access public
     * @param  string   $color_id   Color id
     * @return string
     */
    public function getBorderColor($color_id)
    {
        if (isset($this->default_colors[$color_id])) {
            return $this->default_colors[$color_id]['border'];
        }

        return $this->default_colors[$this->getDefaultColor()]['border'];
    }

    /**
     * Get background color from the color_id
     *
     * @access public
     * @param  string   $color_id   Color id
     * @return string
     */
    public function getBackgroundColor($color_id)
    {
        if (isset($this->default_colors[$color_id])) {
            return $this->default_colors[$color_id]['background'];
        }

        return $this->default_colors[$this->getDefaultColor()]['background'];
    }

    /**
     * Get CSS stylesheet of all colors
     *
     * @access public
     * @return string
     */
    public function getCss()
    {
        $buffer = '';

        foreach ($this->default_colors as $color => $values) {
            $buffer .= 'div.color-'.$color.' {';
            $buffer .= 'background-color: '.$values['background'].';';
            $buffer .= 'border-color: '.$values['border'];
            $buffer .= '}';
            $buffer .= 'td.color-'.$color.' { background-color: '.$values['background'].'}';
        }

        return $buffer;
    }
}
