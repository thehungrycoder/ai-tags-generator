<?php

add_action('admin_menu', 'ai_tags_generator_menu');

function ai_tags_generator_menu() {
    add_options_page(
        'AI Tags Generator Options',
        'AI Tags Generator',
        'manage_options',
        'ai-tags-generator',
        'ai_tags_generator_options'
    );
}

function ai_tags_generator_options() {
    if (!current_user_can('manage_options'))  {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    echo '<div class="wrap">';
    echo '<p>Enter your OpenAI API Key here.</p>';
    echo '<form method="post" action="options.php">';
    settings_fields('ai_tags_generator_options');
    do_settings_sections('ai-tags-generator');
    submit_button();
    echo '</form>';
    echo '</div>';
}

add_action('admin_init', 'register_ai_tags_generator_settings');

function register_ai_tags_generator_settings() {
    register_setting('ai_tags_generator_options', 'openai_api_key');
    add_settings_section('ai_tags_generator_main', 'Main Settings', null, 'ai-tags-generator');
    add_settings_field('openai_api_key', 'OpenAI API Key', 'openai_api_key_callback', 'ai-tags-generator', 'ai_tags_generator_main');
}

function openai_api_key_callback() {
    $setting = esc_attr(get_option('openai_api_key'));
    echo "<input type='text' name='openai_api_key' value='$setting' />";
}
