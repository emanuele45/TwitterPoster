<?php

class TwitterPoster_Integrate
{
	public static function integrate_create_topic($msgOptions, $topicOptions, $posterOptions)
	{
		global $modSettings, $txt, $context;

		if (empty($modSettings['twitter_select_boards']))
			return;

		$enabled_boards = unserialize($modSettings['twitter_select_boards']);

		if (empty($enabled_boards) || !in_array($topicOptions['board'], $enabled_boards))
			return;

		require_once(EXTDIR . '/TwitterOAuth/TwitterOAuth.php');
		loadLanguage('TwitterPoster');

		/**
		* Array with the OAuth tokens provided by Twitter
		* - consumer_key Twitter API key
		* - consumer_secret Twitter API secret
		* - oauth_token Twitter Access token
		* - oauth_token_secret Twitter Access token secret
		*/
		$credentials = array(
		'consumer_key' => $modSettings['consumer_key'],
		'consumer_secret' => $modSettings['consumer_secret'],
		'oauth_token' => $modSettings['oauth_token'],
		'oauth_token_secret' => $modSettings['oauth_token_secret'],
		);

		$find = array(
			'{MEMBERNAME}' => $posterOptions['name'],
			'{SUBJECT}' => $msgOptions['subject'],
			'{FORUM_NAME}' => $context['forum_name'],
			'{TOPIC_URL}' => $scripturl . '?topic=' . $topicOptions['id'] . '.0',
		);

		if (!empty($modSettings['twitter_new_topic']))
			$message = $modSettings['twitter_new_topic'];
		else
			$message = $txt['twitter_new_topic_default'];

		$response = initTwitterOAuth($credentials, str_replace(array_keys($find), array_values($find), $message));
	}

	public static function integrate_general_mod_settings(&$config_vars)
	{
		global $txt, $modSettings;

		loadLanguage('TwitterPoster');

		if (empty($modSettings['twitter_new_topic']))
			$modSettings['twitter_new_topic'] = $txt['twitter_new_topic_default'];

		$config_vars[] = array('title', 'twitter_configs');
		$config_vars[] = array('text', 'consumer_key');
		$config_vars[] = array('text', 'consumer_secret');
		$config_vars[] = array('text', 'oauth_token');
		$config_vars[] = array('text', 'oauth_token_secret');
		$config_vars[] = array('large_text', 'twitter_new_topic', 'subtext' => $txt['twitter_new_topic_desc']);

		require_once(SUBSDIR . '/Boards.subs.php');
		$boardListOpt = array(
			'access' => '-1',
			'override_permissions' => true,
			'not_redirection' => true,
			'ignore' => !empty($modSettings['recycle_enable']) ? array($modSettings['recycle_board']) : null
		);
		$boards_structure = getBoardList($boardListOpt);
		$select = array();

		foreach ($boards_structure['categories'] as $category)
		{
			if (empty($category['boards']))
				continue;
			$select_tmp = array();
			foreach ($category['boards'] as $board)
			{
				if ($board['allow'])
				{
					$select_tmp['b_' . $board['id']] = ($board['child_level'] > 0 ? str_repeat('=', $board['child_level']) . '> ' : '') . $board['name'];
				}
			}

			if (!empty($select_tmp))
			{
				$select['c1_' . $category['id']] = '----------';
				$select['c2_' . $category['id']] = $category['name'];
				$select['c3_' . $category['id']] = '----------';
				$select += $select_tmp;
			}
		}

		if (empty($modSettings['twitter_select_boards']))
			$modSettings['twitter_select_boards'] = serialize(array());
		elseif (!is_array($modSettings['twitter_select_boards']))
		{
			$tmp = unserialize($modSettings['twitter_select_boards']);
			$tmpr = array();
			foreach ($tmp as $b)
				$tmpr[] = 'b_' . $b;
			$modSettings['twitter_select_boards'] = serialize($tmpr);
		}

		$config_vars[] = array('select', 'twitter_select_boards', $select, 'multiple' => true);
	}

	public static function integrate_save_general_mod_settings()
	{
		if (!empty($_POST['twitter_select_boards']))
		{
			$result = array();
			foreach ($_POST['twitter_select_boards'] as $selected)
			{
				if ($selected[0] == 'b')
				{
					$b = explode('_', $selected);
					$result[] = (int) $b[1];
				}
			}
			$result = array_filter($result);
			updateSettings(array('twitter_select_boards' => serialize($result)));
			unset($_POST['twitter_select_boards']);
		}
	}
}