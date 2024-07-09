<?php

class PodsExporterAddon extends \ImportWP\Common\AddonAPI\ExporterAddon
{
    public function export_types()
    {
        return ['post_type', 'taxonomy', 'user'];
    }

    public function get_fields($type, $template_arg = [])
    {
        switch ($type) {
            case 'user':
                $fields = iwp_pods_get_fields('user', 'user');
            case 'term':
                $fields = [];
                $taxonomies = (array)$template_arg;
                foreach ($taxonomies as $taxonomy) {
                    $fields = array_merge($fields, iwp_pods_get_fields($taxonomy, 'taxonomy'));
                }
            default:
                $post_types = (array)$template_arg;
                $fields = [];
                foreach ($post_types as $post_type) {
                    $fields = array_merge($fields, iwp_pods_get_fields($post_type, 'post_type'));
                }
        }

        return $fields;
    }

    public function export_schema($exporter)
    {
        if ($exporter_group = $exporter->register_group('Pods', 'pods')) {

            $type = $exporter->get_type();
            $template_args = $exporter->get_args();

            $fields = $this->get_fields($type, $template_args);
            foreach ($fields as $field_data) {

                if ($field_data['type'] == 'attachment') {
                    $exporter_group->add_field($field_data['meta']);
                    $exporter_group->add_field($field_data['meta'] . '::id');
                    $exporter_group->add_field($field_data['meta'] . '::url');
                } elseif (get_post_meta($field_data['object_id'], 'type', true) === 'pick') {

                    $exporter_group->add_field($field_data['meta']);
                    $pick_object = get_post_meta($field_data['object_id'], 'pick_object', true);

                    switch ($pick_object) {
                        case 'post_type':
                            $exporter_group->add_field($field_data['meta'] . '::id');
                            $exporter_group->add_field($field_data['meta'] . '::name');
                            $exporter_group->add_field($field_data['meta'] . '::slug');
                            break;
                        case 'taxonomy':
                            break;
                        case 'user':
                            break;
                    }
                } else {
                    $exporter_group->add_field($field_data['meta']);
                }
            }
        }
    }

    public function export_data($exporter)
    {
        if ($exporter_group = $exporter->get_group('pods')) {

            $record = $exporter->get_record();

            $type = $exporter->get_type();
            $template_args = $exporter->get_args();

            if ($type == 'user') {
                $pod = pods('user', $record['ID']);
            } else {
                $pod = pods($template_args, $record['ID']);
            }

            $fields = $this->get_fields($type, $template_args);
            foreach ($fields as $field_data) {

                if ($field = $exporter_group->get_field($field_data['meta'])) {

                    if ($value = $pod->field($field->get_id(), null, true)) {



                        if ($field_data['type'] == 'attachment') {

                            if ($field_data['repeatable']) {
                                $value = array_reduce($value, function ($carry, $item) {

                                    $carry[] = $item['ID'];
                                    return $carry;
                                }, []);

                                $field->set_value($value);
                                $field->set_value($value, 'id');
                                $field->set_value(array_map('wp_get_attachment_url', $value), 'url');
                            } else {

                                $field->set_value($value['ID']);
                                $field->set_value($value['ID'], 'id');
                                $field->set_value(wp_get_attachment_url($value['ID']), 'url');
                            }
                        } elseif (get_post_meta($field_data['object_id'], 'type', true) === 'pick') {

                            $pick_object = get_post_meta($field_data['object_id'], 'pick_object', true);

                            switch ($pick_object) {
                                case 'post_type':
                                    if ($field_data['repeatable']) {
                                        $id = array_reduce($value, function ($carry, $item) {

                                            $carry[] = $item['ID'];
                                            return $carry;
                                        }, []);
                                        $field->set_value($id);
                                        $field->set_value($id, 'id');

                                        $name = array_reduce($value, function ($carry, $item) {

                                            $carry[] = $item['post_title'];
                                            return $carry;
                                        }, []);
                                        $field->set_value($name, 'name');

                                        $slug = array_reduce($value, function ($carry, $item) {

                                            $carry[] = $item['post_name'];
                                            return $carry;
                                        }, []);
                                        $field->set_value($slug, 'slug');
                                    } else {
                                        $field->set_value($value['ID']);
                                        $field->set_value($value['ID'], 'id');
                                        $field->set_value($value['post_title'], 'name');
                                        $field->set_value($value['post_name'], 'slug');
                                    }
                                    break;
                                case 'taxonomy':
                                    break;
                                case 'user':
                                    break;
                            }
                        } else {
                            $field->set_value($value);
                        }
                    }
                }
            }
        }
    }
}
