<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    public $name;
    public $class;
    public $width;
    public $height;

    /**
     * Create a new component instance.
     */
    public function __construct($name, $class = '', $width = 20, $height = 20)
    {
        $this->name = $name;
        $this->class = $class;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $path = base_path("assets/frontend/icons/{$this->name}.svg");
        if (!file_exists($path)) {
            $path = public_path("assets/frontend/icons/{$this->name}.svg");
        }
        if (file_exists($path)) {
            $svg = file_get_contents($path);
            
            // Add custom classes or attributes if provided
            $brandIcons = ['facebook', 'instagram', 'linkedin', 'pinterest', 'twitter', 'youtube', 'threads'];
            $isBrand = in_array($this->name, $brandIcons);
            $classes = ($isBrand ? 'brand-icon brand-' : 'lucide lucide-') . $this->name . ($this->class ? ' ' . $this->class : '');
            
            // Replace attributes dynamically if they exist (handling newlines after <svg)
            if (preg_match('/class="[^"]*"/i', $svg)) {
                $svg = preg_replace('/class="[^"]*"/i', 'class="' . $classes . '"', $svg);
            } else {
                $svg = preg_replace('/<svg/i', '<svg class="' . $classes . '"', $svg);
            }
            
            // Inject or replace width attribute
            if (preg_match('/(?<!-)width="[^"]*"/i', $svg)) {
                $svg = preg_replace('/(?<!-)width="[^"]*"/i', 'width="' . $this->width . '"', $svg);
            } else {
                $svg = preg_replace('/<svg/i', '<svg width="' . $this->width . '"', $svg);
            }
            
            // Inject or replace height attribute
            if (preg_match('/(?<!-)height="[^"]*"/i', $svg)) {
                $svg = preg_replace('/(?<!-)height="[^"]*"/i', 'height="' . $this->height . '"', $svg);
            } else {
                $svg = preg_replace('/<svg/i', '<svg height="' . $this->height . '"', $svg);
            }
            
            return $svg;
        }
        
        // Fallback placeholder if icon not found
        return '<!-- Icon not found: ' . e($this->name) . ' -->';
    }
}
