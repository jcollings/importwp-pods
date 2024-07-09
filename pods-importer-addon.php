<?php

class PodsImporterAddon extends \ImportWP\Common\AddonAPI\ImporterAddon
{
    private $_prefix = 'pods';

    public function get_importer_fields()
    {
        $importer_model = $this->get_importer();
        $template = $importer_model->getTemplate();
        switch ($template) {
            case 'user':
                return iwp_pods_get_fields('user', 'user');
            case 'term':
                $taxonomy = $importer_model->getSetting('taxonomy');
                return iwp_pods_get_fields($taxonomy, 'taxonomy');
            default:
                $post_type = $importer_model->getSetting('post_type');
                return iwp_pods_get_fields($post_type, 'post_type');
        }
    }

    public function get_pod($id)
    {
        $importer_model = $this->get_importer();
        $template = $importer_model->getTemplate();
        switch ($template) {
            case 'user':
                return pods('user');
            case 'term':
                $taxonomy = $importer_model->getSetting('taxonomy');
                return pods($taxonomy, $id);
            default:
                $post_type = $importer_model->getSetting('post_type');
                return pods($post_type, $id);
        }
    }

    public function register($template)
    {
        // Register custom fields
        if ($custom_fields = $template->register_custom_fields('Pods', $this->_prefix)) {

            $fields = $this->get_importer_fields();
            foreach ($fields as $field_data) {
                $custom_fields->register_field($field_data['group'] . ' - ' . $field_data['name'], [
                    'id' => $field_data['id'],
                    'type' => $field_data['type']
                ]);
            }
        }
    }

    public function save($data)
    {
        // Save custom fields
        if ($custom_fields = $data->get_custom_fields($this->_prefix)) {

            $fields = $this->get_importer_fields();
            $pod = $this->get_pod($data->get_id());

            foreach ($fields as $field_data) {
                if ($value = $custom_fields->get_value($field_data['id'])) {

                    $data->delete_meta($field_data['meta']);

                    if ($field_data['type'] == 'attachment') {

                        if (!empty($value)) {
                            $ids = [];
                            $urls = [];
                            foreach ($value as $attachment_id) {
                                $ids[] = $attachment_id;
                                $urls[] = wp_get_attachment_url($attachment_id);

                                // only insert first value
                                if (!$field_data['repeatable']) {
                                    break;
                                }
                            }
                            $pod->save($field_data['meta'], $ids, $data->get_id());
                            $data->log($field_data['meta'] . ': ' . implode(',', $urls), $field_data['group']);
                        } else {
                            $data->delete_meta('_pods_' . $field_data['meta']);
                        }
                    } else {

                        if ($field_data['repeatable']) {

                            // should be stored as multiple of the same keys
                            $parts = explode(",", $value);
                            $pod->save($field_data['meta'], $parts, $data->get_id());
                        } else {

                            // $parts = [$value];
                            $pod->save($field_data['meta'], $value, $data->get_id());
                        }

                        $data->log($field_data['meta'], $field_data['group']);
                    }
                }
            }
        }
    }
}
