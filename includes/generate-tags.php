<?php

function generate_ai_tags($post_id, $post, $update) {
  $content = $post->post_content;
  $categories = wp_get_post_categories($post_id);
  $category_names = array();
  foreach ($categories as $category) {
    $category_names[] = $category->name;
  }

  $openai_api_key = get_option('openai_api_key');
  $joined_category_names = implode(', ', $category_names);
  $prompt = "Given following post content, provide me 10 comma separated tags relevant to this post that is good for SEO. Each tag must be 2 words at max and must contain only alphanum, dash characters only.:\n\n Post Content: \"$content\"\n\n Categories: \"$joined_category_names\"";

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  $payload = array(
    "messages" => array(
        array(
            "role" => "user",
            "content" => $prompt
        )
    ),
    "model" => "gpt-3.5-turbo-0301"
);

  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . $openai_api_key
  ));

  $response = curl_exec($ch);

  if (curl_errno($ch)) {
      error_log('Curl error: ' . curl_error($ch));
      return;
  }

  curl_close($ch);

  $response_data = json_decode($response, true);
  $tags_text = $response_data['choices'][0]['message']['content'];
  $tags = explode(', ', $tags_text);

  wp_set_post_tags($post_id, $tags, false);
}


add_action('save_post', 'generate_ai_tags', 10, 3);

function add_generate_tags_action($actions, $post) {
    $actions['generate_tags'] = '<a href="' . admin_url('admin.php?page=generate-tags&post=' . $post->ID) . '" title="Generate Tags">Generate Tags</a>';
    return $actions;
}

add_filter('post_row_actions', 'add_generate_tags_action', 10, 2);

add_action('admin_menu', 'register_generate_tags_page');

function register_generate_tags_page() {
    add_submenu_page('', 'Generate Tags', 'Generate Tags', 'manage_options', 'generate-tags', 'generate_tags_callback');
}

function generate_tags_callback() {
    $post_id = $_GET['post'];
    $post = get_post($post_id);
    generate_ai_tags($post_id, $post, false);
    wp_redirect(admin_url('edit.php'));
    exit;
}
