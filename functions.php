<?php

function iwp_pods_get_fields($section, $section_type = 'post_type')
{
    $options = [];

    $post_query = new WP_Query([
        'post_type' => '_pods_pod',
        'name' => $section,
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

                switch ($field_type) {
                    case 'file':
                        $repeatable = get_post_meta($field_post->ID, 'file_format_type', true) == 'multi';
                        $type = 'attachment';
                        break;
                    default:
                        $repeatable = get_post_meta($field_post->ID, 'repeatable', true) == '1';
                        $type = 'text';
                        break;
                }

                $options[] = [
                    'id' => $field_post->ID . '-' . $field_post->post_name,
                    'meta' => $field_post->post_name,
                    'group' => $post_query->posts[0]->post_title,
                    'name' => $field_post->post_title,
                    'type' => $type,
                    'field' => $field_post,
                    'repeatable' => $repeatable,
                    'object_id' => $field_post->ID
                ];
            }
        }
    }
    return $options;
}
