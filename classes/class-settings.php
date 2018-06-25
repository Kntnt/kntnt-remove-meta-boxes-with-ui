<?php

namespace Kntnt\Remove_Meta_Boxes;

require_once __DIR__ . '/class-abstract-settings.php';

class Settings extends Abstract_Settings {

  public function __construct($plugin) {
    add_action('in_admin_header', [$this, 'do_meta_boxes']);    
  }

  public function do_meta_boxes() {

    global $wp_meta_boxes;  

    if (!$wp_meta_boxes) return;
    
    $opt = Plugin::option();
    unset($opt['loglevel']);

    foreach($opt as $box_id => $screens) {
      foreach ($screens as $screen_id) {
        if (isset($wp_meta_boxes[$screen_id])) {
          foreach ($wp_meta_boxes[$screen_id] as $context_id => $context) {
            foreach ($context as $priority => $boxes) {
              $meta_boxes[$box_id][$screen_id] = $box_id; // TODO: Assign box title instead of box id.
              unset($wp_meta_boxes[$screen_id][$context_id][$priority][$box_id]);
            }
          }
        }
      }
    }

    foreach ($wp_meta_boxes as $screen_id => $screen) {
      foreach ($screen as $context_id => $context) {
        foreach ($context as $priority => $boxes) {
          foreach ($boxes as $box_id => $box) {
            $meta_boxes[$box_id][$screen_id] = $box_id; // TODO $box['title'];
          }
        }
      }
    }

    set_transient(Plugin::ns(), $meta_boxes, 30 * MINUTE_IN_SECONDS);
    
  }

  // Returns title used as menu item and as head of settings page.
  protected function title() {
    return __('Remove Meta Boxes', 'kntnt-remove-meta-boxes');
  }
  
  // Returns all fields used on the settigs page.
  protected function form() {

    $fields['message'] = [
      'type' => 'html',
      'label' => __('IMPORTANT:', 'kntnt-remove-meta-boxes'),
      'html' => '<p style="font-weight: bold;">' . __('Go to where the meta boxes you want to remove exists, e.g. a post, come back here again and select meta box you want to remove.', 'kntnt-remove-meta-boxes') . '</p>',
    ];

    if ($meta_boxes = get_transient(Plugin::ns())) {
      foreach($meta_boxes as $box_id => $screens) {
        $fields[$box_id] = [
          'type'        => 'select multiple',
          'options'     => array_combine(array_keys($screens), array_keys($screens)),
          'default'     => [],
          'label'       => reset($screens),
          'description' => __('Hold down the <em>Ctrl</em>-button (<em>Command</em>-button on Mac) to select or unseelct options.', 'kntnt-remove-meta-boxes'),
        ];
      }
    }

    return $fields;

  }

}
