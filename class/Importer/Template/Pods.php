<?php

namespace ImportWPAddon\Pods\Importer\Template;

use ImportWP\Common\Model\ImporterModel;
use ImportWP\EventHandler;
use WP_Query;

class Pods
{

    /**
     * @var CustomFields $custom_fields
     */
    private $custom_fields;

    public function __construct(EventHandler $event_handler)
    {
        $event_handler;
        $event_handler->listen('importer.custom_fields.init', [$this, 'init']);
        $event_handler->listen('importer.custom_fields.get_fields', [$this, 'get_fields']);
        $event_handler->listen('importer.custom_fields.process_field', [$this, 'process_field']);

        add_filter('iwp/custom_field_key', [$this, 'get_custom_field_key'], 10, 3);
    }

    public function init($result, $custom_fields)
    {
        $this->custom_fields = $custom_fields;
    }

    public function get_pods_fields($section, $section_type = 'post_type')
    {
        $options = [];

        $post_query = new WP_Query([
            'post_type' => '_pods_pod',
            'name' => $section,
            // 'fields' => 'ids',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'type',
                    'value' => $section_type
                ]
            ]
        ]);
        if ($post_query->have_posts() && $post_query->found_posts == 1) {
            $field_query = new WP_Query([
                'post_type' => '_pods_field',
                'post_parent' => $post_query->posts[0]->ID,
                'posts_per_page' => -1,
            ]);

            if ($field_query->have_posts()) {

                foreach ($field_query->posts as $field_post) {

                    $field_type = get_post_meta($field_post->ID, 'type', true);
                    // $file_format_type = get_post_meta($field_post->ID, 'file_format_type', true);

                    switch ($field_type) {
                        case 'file':
                            $type = 'attachment';
                            break;
                        default:
                            $type = 'text';
                            break;
                    }

                    $options[] = [
                        'value' => 'pods_field::' . $type . '::' . $field_post->ID . '-' . $field_post->post_name,
                        'label' => 'Pods - ' . $post_query->posts[0]->post_title . ' - ' . $field_post->post_title
                    ];
                }
            }
        }
        return $options;
    }

    public function get_fields($fields, ImporterModel $importer_model)
    {
        $template = $importer_model->getTemplate();
        switch ($template) {
            case 'user':
                $fields = array_merge($fields, $this->get_pods_fields('user', 'user'));
                break;
            case 'term':
                $taxonomy = $importer_model->getSetting('taxonomy');
                $fields = array_merge($fields, $this->get_pods_fields($taxonomy, 'taxonomy'));
                break;
            default:
                $post_type = $importer_model->getSetting('post_type');
                $fields = array_merge($fields, $this->get_pods_fields($post_type, 'post_type'));
                break;
        }

        return $fields;
    }

    public function process_field($result, $post_id, $key, $value, $custom_field_record, $prefix, $importer_model, $custom_field)
    {
        if (strpos($key, 'pods_field::') !== 0) {
            return $result;
        }

        $field_key = $this->get_custom_field_key($key);
        $field_id = $this->get_custom_field_id($key);

        $field_type = get_post_meta($field_id, 'type', true);
        if ($field_type == 'file') {
            $custom_field_record[$prefix . '_return'] = 'id';
            $value = $this->custom_fields->processAttachmentField($value, $post_id, $custom_field_record, $prefix);
        }

        $result[$field_key] = $value;
        return $result;
    }

    /**
     * @param string $key
     * @param TemplateInterface $template
     * @param ImporterModel $importer
     * @return string
     */
    public function get_custom_field_key($key, $template = null, $importer = null)
    {
        if (strpos($key, 'pods_field::') !== 0) {
            return $key;
        }

        $field_key = substr($key, strrpos($key, '::') + strlen('::'));

        $matches = [];
        if (preg_match('/^\d+-(.*?)$/', $field_key, $matches) !== false) {
            return $matches[1];
        }

        return $key;
    }

    /**
     * @param string $key
     * @return int
     */
    public function get_custom_field_id($key)
    {
        if (strpos($key, 'pods_field::') !== 0) {
            return false;
        }

        $field_key = substr($key, strrpos($key, '::') + strlen('::'));

        $matches = [];
        if (preg_match('/^(\d+)-(.*?)$/', $field_key, $matches) !== false) {
            return $matches[1];
        }

        return false;
    }
}
