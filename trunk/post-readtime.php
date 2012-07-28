<?php

	/* 
		Plugin Name: Post Reading Time
		Plugin URI: http://wpplugz.is-leet.com
		Description: A simple wordpress plugin that estimates the time a reader will need to go through the article.
		Version: 0.1
		Author: Bostjan Cigan
		Author URI: http://bostjan.gets-it.net
		License: GPL v2
	*/ 

	// First we register all the functions
	register_activation_hook(__FILE__, 'post_readtime_install');
	register_deactivation_hook(__FILE__, 'post_readtime_uninstall');
	add_action('admin_menu', 'post_readtime_admin_menu_create');
	
	// Options when activating the plugin
	function post_readtime_install() {
		add_option('post_readtime_prefix', 'Estimated reading time: ', '', 'yes'); // Add the option for prefix to string
		add_option('post_readtime_suffix', '', '', 'yes'); // Add the option for suffix to string
		add_option('post_readtime_wpm', '200', '', 'yes'); // Add the words per second option (default 200 wps)
		add_option('post_readtime_time', '1', '', 'yes'); // Type of time output (minutes or minutes and seconds)
	}
	
	// Options when deactivating the plugin (delete the options from DB)
	function post_readtime_uninstall() {
		delete_option('post_readtime_prefix');
		delete_option('post_readtime_suffix');
		delete_option('post_readtime_wpm');
		delete_option('post_readtime_time');
	}	
	
	function post_readtime_admin_menu_create() {
		add_options_page('Post Read Time Settings', 'Post Reading Time', 'administrator', __FILE__, 'post_readtime_settings');
	}

	// The admin interface
	function post_readtime_settings() {
	
		$message = "";
	
		if(isset($_POST['pr_wpm'])) {
			$wpm = $_POST['pr_wpm'];
			$prefix = html_entity_decode($_POST['pr_prefix']);
			$suffix = html_entity_decode($_POST['pr_suffix']);
			$time = $_POST['pr_time'];
			update_option('post_readtime_prefix', $prefix);
			update_option('post_readtime_suffix', $suffix);
			update_option('post_readtime_wpm', $wpm);
			update_option('post_readtime_time', $time);
			$message = "Options updated.";
		}
		
		$wpm = get_option('post_readtime_wpm');
		$suffix = stripslashes(htmlentities(get_option('post_readtime_suffix')));
		$prefix = stripslashes(htmlentities(get_option('post_readtime_prefix')));
		$time = get_option('post_readtime_time');
		
?>

		<div class="wrap">
			<h2>Post Reading Time Settings</h2>
			<p><strong><?php echo $message; ?></strong></p>
			<form method="post" action="">
				<p><strong>Words per minute</strong> <br /><input type="text" name="pr_wpm" value="<?php echo $wpm; ?>" /></p>
				<p><strong>Prefix</strong> <br /><input type="text" name="pr_prefix" value="<?php echo $prefix; ?>" /> (what is written before the time)</p>
				<p><strong>Suffix</strong> <br /><input type="text" name="pr_suffix" value="<?php echo $suffix; ?>" /> (what is written after the time)</p>
				<p><strong>Time output</strong> <br /><select id="pr_time" name="pr_time">
									<option value="1" <?php if($time == "1") { echo 'selected="selected"'; } ?>>Minutes</option>
									<option value="2" <?php if($time == "2") { echo 'selected="selected"'; } ?>>Minutes and seconds</option>
								</select>
				</p>
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update options') ?>" />
			</form>
			<h3>About</h3>
			<p>Thank you for using this plugin. That means you wanted to have the same thing on your blog than me, to give your readers an estimate on 
			how long they need to read your post.<br />An average man reads 250 - 300 words for minute, so you can change the
			default settings any way you like, the default here is 200 words per minute. That's pretty much it.</p> 
			<p>You can also visit the <a href="http://wpplugz.is-leet.com">homepage</a> for the latest updates.</p>
			<p>To use it, add <pre>< ?php post_read_time(); ? ></pre> to your template (where you want to output the 
			text).</p>

		</div>

<?php

	}
	
	// The actual function that does the work and output the string of the estimated reading time of the post 
	function post_read_time() {
	
		$words_per_second_option = get_option('post_readtime_wpm');
		$prefix = stripslashes(html_entity_decode(get_option('post_readtime_prefix')));
		$suffix = stripslashes(html_entity_decode(get_option('post_readtime_suffix')));
		$time = get_option('post_readtime_time');
	
		$content = get_the_content();
		$num_words = str_word_count(strip_tags($content));
		$minutes = floor($num_words / $words_per_second_option);
		$seconds = floor($num_words % $words_per_second_option / ($words_per_second_option / 60));
		$estimated_time = $prefix;
		if($time == "1") {
			if($seconds >= 30) {
				$minutes = $minutes + 1;
			}
			$estimated_time = $estimated_time.' '.$minutes . ' minute'. ($minutes == 1 ? '' : 's');
		}
		else {
			$estimated_time = $estimated_time.' '.$minutes . ' minute'. ($minutes == 1 ? '' : 's') . ', ' . $seconds . ' second' . ($seconds == 1 ? '' : 's');		
		}
		if($minutes < 1) {
			$estimated_time = $estimated_time." Less than a minute";
		}

		$estimated_time = $estimated_time.$suffix;
		
		echo $estimated_time;

	}

?>
