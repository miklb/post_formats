<?php

namespace Habari;

class PostFormatsPlugin extends Plugin
{

	function configure() {
		$form = new FormUI( strtolower( get_class( $this ) ) );

		$formats = $this->get_formats(true);


		$form->append(FormControlLabel::wrap(_t('Custom Formats(one perline)' ), FormControlTextArea::create('formats', 'post_formats__custom_formats')));
		$form->append(FormControlStatic::create('existing_formats', '<fieldset><legend>' . _t('Current System-Supplied Formats', 'post_formats') . '</legend><ul style="list-style:disc inside;"><li>' . implode('</li><li>', $formats) . '</li></ul></fieldset>'));
		$form->append(FormControlSubmit::create('submit')->set_caption('Submit'));

		return $form;
	}

	/**
	 * @param string $class
	 * @param Post $post
	 * @return string
	 */
	function filter_post_class($class, $post) {
		if(empty($class)) {
			$class = array();
		}
		elseif(is_string($class)) {
			$class = array($class);
		}
		if(isset($post->info->class)) {
			$class[] = $post->info->class;
		}
		$class = array_merge($class, $post->content_type());
		if($post->content_type == Post::type('entry')) {
			if(isset($post->info->format)) {
				array_unshift($class, 'format-' . $post->info->format);
			}
			else {
				array_unshift($class, 'format-standard');
			}
		}
		$class[] = 'hentry';
		return implode(' ', $class);
	}

	function filter_content_type($type, $post) {
		if($post->content_type == Post::type('entry')) {
			if(isset($post->info->format)) {
				array_unshift($type, 'entry.' . $post->info->format);
			}
			else {
				array_unshift($type, 'entry.standard');
			}
		}
		return $type;
	}

	function get_formats($system = false) {
		$formats = array(
			'standard' => 'Standard Entry',
			'aside' => 'Aside',
			'audio' => 'Audio',
			'Chat' => 'Chat',
			'gallery' => 'Gallery',
			'imaage' => 'Image',
			'link' => 'Link',
			'quote' => 'Quote',
			'status' => 'Status',
			'video' => 'Video',
		);

		if(!$system) {
			$custom = Options::get('post_formats__custom_formats', '');
			$custom = explode("\n", $custom);
			$custom = array_map('trim', $custom);
			$custom = array_filter($custom);
			$custom = array_combine(array_map('Habari\Utils::slugify', $custom), $custom);
			$formats = array_merge($formats, $custom);
		}

		$formats = Plugins::filter('get_post_formats', $formats);
		return $formats;
	}


	function action_form_publish_entry($form, $post, $context) {
		$options = $this->get_formats();
		$post_format = $post->info->format;
		if(!isset($options[$post_format]) && $post_format != '') {
			$options[$post_format] = _t('%$1s (unknown', array($post_format), 'post_formats');
		}
		$form->append(
				FormControlSelect::create($post->slug)
				->set_options($options)
				->label(_t('Post Format'))
			);
	}

}

?>
