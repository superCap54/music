<?php
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\MultipartStream;
if(!class_exists("FacebookCookieApi")){

include "Curl.php";
include "PersianStringDecorator.php";
include "PHPImage/PHPImage.php";
class FacebookCookieApi
{
	private $client;
	private $fbUserId;
	private $fbSess;
	private $proxy;
	private $lsd = '';
	private $fb_dtsg = '';

	public function __construct ( $fbUserId, $fbSess, $proxy = NULL, $newPageID = NULL)
	{
		$this->fbUserId = $fbUserId;
		$this->fbSess   = $fbSess;
		$this->proxy    = $proxy;
		$this->newPageID = $newPageID;

		$this->client = new Client( [
			'cookies'         => $this->buildCookies(),
			'allow_redirects' => [ 'max' => 5 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0' ]
		] );

		$this->fb_dtsg();
	}

	private function buildHeaders ( $additionalHeaders = [] )
	{
		$headers = [
			'Accept'                      => '*/*',
			'Accept-Encoding'             => 'gzip',
			'User-Agent'                  => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36',
			'viewport-width'              => 1229,
			'Content-Type'                => 'application/x-www-form-urlencoded',
			'Origin'                      => 'https://www.facebook.com',
			'sec-ch-prefers-color-scheme' => 'light',
			'sec-ch-ua'                   => '".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
			'sec-ch-ua-mobile'            => '?0',
			'sec-ch-ua-platform'          => '"Windows"',
			'Sec-Fetch-Dest'              => 'empty',
			'Sec-Fetch-Mode'              => 'cors',
			'Sec-Fetch-Site'              => 'same-origin',
			'Connection'                  => 'keep-alive',
			'Host'                        => 'www.facebook.com',
			'X-FB-LSD'                    => $this->lsd
		];

		return array_merge( $headers, $additionalHeaders );
	}

	private function buildCookies ()
	{
		$cookies = [
			'c_user'        => $this->fbUserId,
			'xs'            => $this->fbSess,
			'm_page_voice'  => $this->fbUserId,
			'm_pixel_ratio' => '1.5625',
			'dpr'           => '1.5625',
			'oo'            => 'v1',
			'wd'            => '1229x582'
		];

		if ( ! empty( $this->newPageID ) )
		{
			$cookies[ 'i_user' ] = $this->newPageID;
		}

		$cooks = [];

		foreach ( $cookies as $k => $v )
		{
			$cooks[] = [
				"Name"     => $k,
				"Value"    => $v,
				"Domain"   => ".facebook.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			];
		}

		return new CookieJar( FALSE, $cooks );
	}

	private function buildSendData ( $av, $apiFriendlyName, $docID, $variables = [] )
	{
		$sendData = [
			'fb_dtsg'                  => $this->fb_dtsg,
			'lsd'                      => $this->lsd,
			'__user'                   => empty( $this->newPageID ) ? $this->fbUserId : $this->newPageID,
			'av'                       => $av,
			'req'                      => '1c',
			'dpr'                      => '2',
			'__ccg'                    => 'GOOD',
			'__comet_reg'              => '1',
			'serve_timestamps'         => 'true',
			'fb_api_req_friendly_name' => $apiFriendlyName,
			'fb_api_caller_class'      => 'RelayModern',
			'doc_id'                   => $docID
		];

		if ( ! empty( $variables ) )
		{
			$sendData[ 'variables' ] = json_encode( $variables );
		}

		return $sendData;
	}

	public function authorizeFbUser ()
	{
		$myInfo = $this->myInfo();
		return $myInfo;
	}

	public function myInfo ()
	{
		try
		{
			$req = $this->client->request( 'GET', 'https://m.facebook.com/', [
				//'allow_redirects' => [ 'max' => 0 ]
			] );

			$location = $req->getHeader( 'Location' );

			if ( ! empty( $location ) && strpos( $location[ 0 ], '/checkpoint/' ) > -1 )
			{
				return [
					'status' => 'error',
					'message' => __('Your account seems to be blocked by Facebook. You need to unblock it before adding the account.')
				];
			}


			$getInfo = (string) $req->getBody();
		}
		catch ( Exception $e )
		{
			return [
				'status' => 'error',
				'message' => __( $e->getMessage() )
			];
		}



		$avatar = "";
		preg_match_all("/profile picture\" role=\"img\" style=\"background:#d8dce6 url\(\&\#039\;(.*)\&\#039\;\)/U", $getInfo, $all_images);
		if(!empty($all_images)){
			foreach ($all_images as $key => $images) {
				if($key != 0 && !empty($images)){
					$images = array_filter($images);
					$avatar = $images[0];
					$avatar = str_replace("\\\\", "%", $avatar);
					$avatar = str_replace("\\", "%", $avatar);
					$avatar = urldecode($avatar);
					$avatar = preg_replace('/\s+/', '', $avatar);
				}
			}
		}

		preg_match( '/\"USER_ID\"\:\"([0-9]+)\"/i', $getInfo, $accountId );
		$accountId = isset( $accountId[ 1 ] ) ? $accountId[ 1 ] : '?';

		preg_match( '/\"NAME\"\:\"([^\"]+)\"/i', $getInfo, $name );
		$name = json_decode( '"' . ( isset( $name[ 1 ] ) ? $name[ 1 ] : '?' ) . '"' );

		if($avatar == ""){
			$avatar = get_avatar($name, "rand");
		}

		if ( $this->fbUserId !== $accountId )
		{
			return [
				'status' => 'error',
				'message' => __('The same as your Facebook user id incorrect')
			];
		}

		return [
			'status' => 'success',
			'id'   => $accountId,
			'name' => $name,
			'avatar' => $avatar,
		];
	}

	private function page_walk ( $arr, &$store )
	{
		foreach ( $arr as $k => $v )
		{
			if ( $k === 'profile' || $k === 'page_with_default_viewer' )
			{
				if ( ! isset( $v[ '__isProfile' ] ) && isset( $v[ 'name' ] ) && isset( $v[ 'id' ] ) && ( ! isset( $store[ $v[ 'id' ] ] ) || isset( $v[ 'delegate_page_id' ] ) ) )
				{
					$store[ $v[ 'id' ] ][ 'id' ]   = $v[ 'id' ];
					$store[ $v[ 'id' ] ][ 'name' ] = $v[ 'name' ];

					if ( isset( $v[ 'delegate_page_id' ] ) )
					{
						$store[ $v[ 'id' ] ][ 'delegate_page_id' ] = $v[ 'delegate_page_id' ];
					}

					if ( isset( $v[ 'is_profile_plus' ] ) )
					{
						$store[ $v[ 'id' ] ][ 'is_profile_plus' ] = $v[ 'is_profile_plus' ];
					}

					$store[ $v[ 'id' ] ][ 'cover' ] = 'https://graph.facebook.com/' . ( isset( $store[ $v[ 'id' ] ][ 'delegate_page_id' ] ) ? $store[ $v[ 'id' ] ][ 'delegate_page_id' ] : $v[ 'id' ] ) . '/picture';
				}
			}
			else if ( $k === 'admined_pages' )
			{
				if ( isset( $v[ 'nodes' ] ) )
				{
					foreach ( $v[ 'nodes' ] as $node )
					{
						if ( isset( $node[ 'name' ] ) && isset( $node[ 'id' ] ) && ( ! isset( $store[ $node[ 'id' ] ] ) || isset( $node[ 'delegate_page_id' ] ) ) )
						{
							$store[ $node[ 'id' ] ][ 'id' ]   = $node[ 'id' ];
							$store[ $node[ 'id' ] ][ 'name' ] = $node[ 'name' ];

							if ( isset( $node[ 'delegate_page_id' ] ) )
							{
								$store[ $node[ 'id' ] ][ 'delegate_page_id' ] = $node[ 'delegate_page_id' ];
							}

							if ( isset( $node[ 'is_profile_plus' ] ) )
							{
								$store[ $node[ 'id' ] ][ 'is_profile_plus' ] = $node[ 'is_profile_plus' ];
							}

							$store[ $node[ 'id' ] ][ 'cover' ] = 'https://graph.facebook.com/' . ( ( isset( $store[ $node[ 'id' ] ][ 'delegate_page_id' ] ) ? $store[ $node[ 'id' ] ][ 'delegate_page_id' ] : $node[ 'id' ] ) ) . '/picture';
						}
					}
				}
			}
			else if ( is_array( $v ) )
			{
				$this->page_walk( $v, $store );
			}
		}
	}

	public function getMyPages ()
	{
		$myPagesArr = [];

		try
		{
			$result = (string) $this->client->get( 'https://www.facebook.com/pages/?category=your_pages&ref=bookmarks', [
				'headers' => [
					'Accept'         => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
					'User-Agent'     => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
					'Sec-Fetch-Dest' => 'document',
					'Sec-Fetch-Mode' => 'navigate'
				]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match_all( "/<script.*type=\"(?!text\/x-template).*>(.*)<\/script>/im", $result, $matches );
		if ( empty( $matches[ 1 ] ) )
		{
			return $myPagesArr;
		}

		foreach ( $matches[ 1 ] as $m )
		{
			$pw = json_decode( $m, TRUE );

			if ( empty( $pw ) )
			{
				continue;
			}

			$this->page_walk( $pw, $myPagesArr );
		}

		return array_values( $myPagesArr );
	}

	public function getGroups ()
	{
		$listTypes = [
			'ADMIN_MODERATOR_GROUPS',
			'NON_ADMIN_MODERATOR_GROUPS'
		];

		$groups = [];

		foreach ( $listTypes as $listType )
		{
			$variables = [
				'count'    => 10,
				'listType' => $listType,
				'scale'    => 2
			];

			$sendData = $this->buildSendData( $this->fbUserId, 'GroupsLeftRailYourGroupsPaginatedQuery', '5325328520844756', $variables );

			$hasNextPage = TRUE;

			while ( $hasNextPage )
			{
				//sleep(15.0/mt_rand(10, 30));
				try
				{
					$post = (string) $this->client->post( 'https://www.facebook.com/api/graphql/', [
							'query' => $sendData
						]
					)->getBody();

					$groupList = json_decode( $post, TRUE );

					if ( isset( $groupList[ 'data' ][ 'viewer' ][ 'groups_tab' ][ 'tab_groups_list' ][ 'edges' ] ) )
					{
						foreach ( $groupList[ 'data' ][ 'viewer' ][ 'groups_tab' ][ 'tab_groups_list' ][ 'edges' ] as $groupData )
						{
							if ( $groupData[ 'node' ] )
							{
								$groups[] = [
									'id'    => $groupData[ 'node' ][ 'id' ],
									'name'  => $groupData[ 'node' ][ 'name' ],
									'cover' => isset( $groupData[ 'node' ][ 'profile_picture' ][ 'uri' ] ) ? $groupData[ 'node' ][ 'profile_picture' ][ 'uri' ] : NULL
								];
							}
						}

						$hasNextPage = ! empty( $groupList[ 'data' ][ 'viewer' ][ 'groups_tab' ][ 'tab_groups_list' ][ 'page_info' ][ 'has_next_page' ] );

						if ( $groupList[ 'data' ][ 'viewer' ][ 'groups_tab' ][ 'tab_groups_list' ][ 'page_info' ][ 'end_cursor' ] )
						{
							$variables[ 'cursor' ]   = $groupList[ 'data' ][ 'viewer' ][ 'groups_tab' ][ 'tab_groups_list' ][ 'page_info' ][ 'end_cursor' ];
							$sendData[ 'variables' ] = json_encode( $variables );
						}
					}
					else
					{
						break;
					}
				}
				catch ( Exception $e )
				{
					return [];
				}
			}
		}

		return $groups;
	}

	public function getStats ( $postId )
	{
		try
		{
			$result = (string) $this->client->request( 'GET', 'https://touch.facebook.com/' . $postId )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match( '/\,comment_count\:([0-9]+)\,/i', $result, $comments );
		preg_match( '/\,share_count\:([0-9]+)\,/i', $result, $shares );
		preg_match( '/\,reactioncount\:([0-9]+)\,/i', $result, $likes );

		return [
			'like'     => isset( $likes[ 1 ] ) ? $likes[ 1 ] : 0,
			'comments' => isset( $comments[ 1 ] ) ? $comments[ 1 ] : 0,
			'shares'   => isset( $shares[ 1 ] ) ? $shares[ 1 ] : 0,
			'details'  => ''
		];
	}

	public function sendPost ( $nodeFbId, $nodeType, $type, $message, $link, $medias, $posterId )
	{
		$attachedMedia = [];

		if ( $type === 'link' )
		{
			$linkVariables = [
				"feedLocation"                                          => "FEED_COMPOSER",
				"focusCommentID"                                        => NULL,
				"goodwillCampaignId"                                    => "",
				"goodwillCampaignMediaIds"                              => [],
				"goodwillContentType"                                   => NULL,
				"params"                                                => [
					"url" => $link
				],
				"privacySelectorRenderLocation"                         => "COMET_COMPOSER",
				"renderLocation"                                        => "composer_preview",
				"parentStoryID"                                         => NULL,
				"scale"                                                 => 2,
				"useDefaultActor"                                       => FALSE,
				"shouldIncludeStoryAttachment"                          => FALSE,
				"__relay_internal__pv__FBReelsEnableDeferrelayprovider" => FALSE
			];

			$linkSendData = $this->buildSendData(
				empty( $this->newPageID ) ? $this->fbUserId : $this->newPageID,
				empty( $this->newPageID ) ? 'ComposerStoryCreateMutation' : 'ComposerLinkAttachmentPreviewQuery',
				empty( $this->newPageID ) ? '7700513916656935' : '5847144011982556',
				$linkVariables );
			$linkSendData = http_build_query( $linkSendData, '', '&' );

			try
			{
				$post = $this->client->post( 'https://www.facebook.com/api/graphql/', [
					'body'    => $linkSendData,
					'headers' => $this->buildHeaders( [
						'Content-Length'     => strlen( $linkSendData ),
						'X-FB-Friendly-Name' => empty( $this->newPageID ) ? 'ComposerStoryCreateMutation' : 'ComposerLinkAttachmentPreviewQuery',
					] )
				] )->getBody()->getContents();

				$linkInfo = json_decode( $post, TRUE );

				$linkScrapeData = isset( $linkInfo[ 'data' ][ 'link_preview' ][ 'share_scrape_data' ] ) ? $linkInfo[ 'data' ][ 'link_preview' ][ 'share_scrape_data' ] : json_encode( [
					'share_type'   => 100,
					'share_params' => [ 'url' => $link ]
				] );
			}
			catch ( Exception $e )
			{
				$linkScrapeData = json_encode( [ 'share_type' => 100, 'share_params' => [ 'url' => $link ] ] );
			}

			$attachedMedia = [
				[
					'link' => [
						'share_scrape_data' => $linkScrapeData
					]
				]
			];
		}

		if ( $type === 'media' )
		{
			$sendData[ 'photo_ids' ] = [];
			$medias                  = is_array( $medias ) ? $medias : [ $medias ];

			foreach ( $medias as $imageURL )
			{
				$photoId = $this->uploadPhoto( $imageURL, empty( $this->newPageID ) ? $nodeFbId : ( $nodeType == 'group' ? $posterId : $nodeFbId ), $nodeType );

				if ( $photoId == 0 )
				{
					continue;
				}

				$attachedMedia[] = [
					'photo' => [
						'id' => $photoId
					]
				];
			}
		}

		try
		{
			$uuid = self::uuid();

			$variables = [
				"input"                                                 => [
					"composer_entry_point"    => "inline_composer",
					"composer_source_surface" => "timeline",
					"source"                  => "WWW",
					"attachments"             => [],
					"audience"                => [
						"privacy" => [
							"allow"               => [],
							"base_state"          => "EVERYONE",
							"deny"                => [],
							"tag_expansion_state" => "UNSPECIFIED"
						]
					],
					"message"                 => [
						"ranges" => [],
						"text"   => $message
					],
					"with_tags_ids"           => [],
					"inline_activities"       => [],
					"explicit_place_id"       => "0",
					"text_format_preset_id"   => "0",
					"logging"                 => [
						"composer_session_id" => $uuid
					],
					"navigation_data"         => [
						"attribution_id_v2" => "ProfileCometTimelineListViewRoot.react,comet.profile.timeline.list,unexpected,1658391971861,956325,190055527696468;CometHomeRoot.react,comet.home,via_cold_start,1658391840657,922327,4748854339"
					],
					"tracking"                => [
						NULL
					],
					"actor_id"                => "$nodeFbId",
					"client_mutation_id"      => "1"
				],
				"displayCommentsFeedbackContext"                        => NULL,
				"displayCommentsContextEnableComment"                   => NULL,
				"displayCommentsContextIsAdPreview"                     => NULL,
				"displayCommentsContextIsAggregatedShare"               => NULL,
				"displayCommentsContextIsStorySet"                      => NULL,
				"feedLocation"                                          => "TIMELINE",
				"feedbackSource"                                        => 0,
				"focusCommentID"                                        => NULL,
				"gridMediaWidth"                                        => 230,
				"groupID"                                               => NULL,
				"scale"                                                 => 2,
				"privacySelectorRenderLocation"                         => "COMET_STREAM",
				"renderLocation"                                        => "timeline",
				"useDefaultActor"                                       => FALSE,
				"inviteShortLinkKey"                                    => NULL,
				"isFeed"                                                => FALSE,
				"isFundraiser"                                          => FALSE,
				"isFunFactPost"                                         => FALSE,
				"isGroup"                                               => FALSE,
				"isEvent"                                               => FALSE,
				"isTimeline"                                            => FALSE,
				"isSocialLearning"                                      => FALSE,
				"isPageNewsFeed"                                        => FALSE,
				"isProfileReviews"                                      => FALSE,
				"isWorkSharedDraft"                                     => FALSE,
				"UFI2CommentsProvider_commentsKey"                      => "ProfileCometTimelineRoute",
				"hashtag"                                               => NULL,
				"canUserManageOffers"                                   => FALSE,
				"__relay_internal__pv__FBReelsEnableDeferrelayprovider" => FALSE
			];

			if ( $nodeType === 'profile' )
			{
				$variables[ 'isTimeline' ]                   = TRUE;
				$variables[ 'input' ][ 'idempotence_token' ] = $uuid . "_FEED";

				$av              = $this->fbUserId;
				$docID           = '4762364973863293';
				$apiFriendlyName = 'ComposerStoryCreateMutation';
			}
			else if ( $nodeType === 'page' )
			{
				$variables[ 'isFeed' ]                                            = TRUE;
				$variables[ 'UFI2CommentsProvider_commentsKey' ]                  = 'CometModernHomeFeedQuery';
				$variables[ 'renderLocation' ]                                    = 'homepage_stream';
				$variables[ 'input' ][ 'idempotence_token' ]                      = $uuid . "_FEED";
				$variables[ 'feedbackSource' ]                                    = 1;
				$variables[ 'gridMediaWidth' ]                                    = NULL;
				$variables[ 'input' ][ 'composer_source_surface' ]                = 'newsfeed';
				$variables[ 'input' ][ 'navigation_data' ][ 'attribution_id_v2' ] = 'CometHomeRoot.react,comet.home,via_cold_start,1663822873505,117673,4748854339,';
				$variables[ 'feedLocation' ]                                      = 'NEWSFEED';

				$av              = $nodeFbId;
				$docID           = '5615191498501965';
				$apiFriendlyName = 'ComposerStoryCreateMutation';
			}
			else
			{
				$variables[ 'isGroup' ]                                           = TRUE;
				$variables[ 'UFI2CommentsProvider_commentsKey' ]                  = 'CometGroupDiscussionRootSuccessQuery';
				$variables[ 'renderLocation' ]                                    = 'group';
				$variables[ 'gridMediaWidth' ]                                    = NULL;
				$variables[ 'input' ][ 'composer_source_surface' ]                = 'group';
				$variables[ 'input' ][ 'navigation_data' ][ 'attribution_id_v2' ] = 'CometGroupDiscussionRoot.react,comet.group,tap_bookmark,1663824614770,694937,462245802259084,';
				$variables[ 'feedLocation' ]                                      = 'GROUP';
				$variables[ 'input' ][ 'audience' ]                               = [
					'to_id' => (string) $nodeFbId
				];

				$variables[ 'input' ][ 'actor_id' ] = empty( $posterId ) ? $this->fbUserId : $posterId;

				$av              = empty( $this->newPageID ) ? $this->fbUserId : ( empty( $posterId ) ? $this->fbUserId : $posterId );
				$docID           = '7977194912351758';
				$apiFriendlyName = 'ComposerStoryCreateMutation';
			}

			$variables[ 'input' ][ 'attachments' ] = $attachedMedia;

			$sendData = $this->buildSendData( $av, $apiFriendlyName, $docID, $variables );
			$sendData = http_build_query( $sendData, '', '&' );

			$post = (string) $this->client->post( 'https://www.facebook.com/api/graphql/', [
				'headers' => $this->buildHeaders( [
					'Content-Length'     => strlen( $sendData ),
					'X-FB-Friendly-Name' => 'ComposerStoryCreateMutation'
				] ),
				'body'    => $sendData
			] )->getBody();

			preg_match( '/legacy_story_hideable_id\":\"([0-9]+?)\"/', $post, $matches );

			if ( isset( $matches[ 1 ] ) )
			{
				return [
					'status' => 'success',
					'id'     => $matches[ 1 ]
				];
			}

			$parsedError = $this->parseErrors( $post );

			if ( $parsedError !== FALSE )
			{
				return [
					'status'    => 'error',
					'message' => $parsedError
				];
			}

			return [
				'status'    => 'error',
				'message' => __("An error occured while sharing the post." )
			];
		}
		catch ( \Exception $e )
		{
			return [
				'status'    => 'error',
				'message' => $e->getMessage()
			];
		}
	}

	private function parseErrors ( $post )
	{
		$postDecoded = json_decode( $post, TRUE );

		if ( isset( $postDecoded[ 'errors' ][ 0 ][ 'description' ] ) )
		{
			$desc = $postDecoded[ 'errors' ][ 0 ][ 'description' ];

			if ( is_array( $desc ) && isset( $desc[ '__html' ] ) && is_string( $desc[ '__html' ] ) )
			{
				return htmlspecialchars( $desc[ '__html' ] );
			}
			else if ( is_array( $desc ) )
			{
				return htmlspecialchars( json_encode( $desc[ '__html' ] ) );
			}
			else
			{
				return $desc;
			}
		}
		else
		{
			preg_match( '/errorDescription\":\"(.+?)\"/', $post, $matches );

			if ( isset( $matches[ 1 ] ) )
			{
				return $matches[ 1 ];
			}
		}

		return FALSE;
	}

	private function fb_dtsg ()
	{
		if ( empty( $this->fb_dtsg ) )
		{
			try
			{
				$getFbDtsg = $this->client->get( 'https://facebook.com/' )->getBody();
			}
			catch ( Exception $e )
			{
				$getFbDtsg = '';
			}

			preg_match( '/DTSGInitialData\",\[],\{\"token\":\"(.+?)\"}/', $getFbDtsg, $fb_dtsg );
			preg_match( '/LSD\",\[],\{\"token\":\"(.+?)\"}/', $getFbDtsg, $LSD );

			if ( isset( $fb_dtsg[ 1 ] ) )
			{
				$this->fb_dtsg = $fb_dtsg[ 1 ];
			}

			if ( isset( $LSD[ 1 ] ) )
			{
				$this->lsd = $LSD[ 1 ];
			}

			if ( strpos( $getFbDtsg, 'cookie/consent' ) > -1 )
			{
				try
				{
					$this->client->post( 'https://www.facebook.com/cookie/consent/', [
						'form_params' => [
							'fb_dtsg'        => $this->fb_dtsg(),
							'__a'            => '1',
							'__user'         => $this->fbUserId,
							'accept_consent' => 'true',
							'__ccg'          => 'GOOD'
						]
					] );
				}
				catch ( Exception $e )
				{
					return '';
				}
			}
		}

		return $this->fb_dtsg;
	}

	private function uploadPhoto ( $photo, $target, $targetType )
	{
		$query = [
			'av'      => ! empty( $this->newPageID ) || $targetType == 'ownpage' ? $target : $this->fbUserId,
			'__user'  => empty( $this->newPageID ) ? $this->fbUserId : $target,
			'__a'     => 1,
			'__req'   => '3l',
			'dpr'     => 2,
			'__ccg'   => 'EXCELLENT',
			'fb_dtsg' => $this->fb_dtsg,
			'lsd'     => $this->lsd
		];


		$basename = basename( $photo );

		if ( strpos( $basename, '.' ) === FALSE )
		{
			$basename = $basename . '.jpg';
			$img      = file_get_contents( $photo );
		}
		else
		{
			$img = Curl::getURL( $photo, $this->proxy );
		}

		$postData = [
			[
				'name'     => 'source',
				'contents' => 8
			],
			[
				'name'     => 'profile_id',
				'contents' => empty( $this->newPageID ) ? $this->fbUserId : $target
			],
			[
				'name'     => 'waterfallxapp',
				'contents' => 'comet',
			],
			[
				'name'     => 'farr',
				'contents' => $img,
				'filename' => $basename
			],
			[
				'name'     => 'upload_id',
				'contents' => 'jsc_c_jh'
			]
		];

		$endpoint = 'https://upload.facebook.com/ajax/react_composer/attachments/photo/upload?' . http_build_query( $query, '', '&' );

		$body = new MultipartStream(
			$postData,
			'------WebKitFormBoundaryEDnegskZbO29yK7o'
		);

		try
		{
			$post = $this->client->post( $endpoint, [
				'body'    => $body,
				'headers' => $this->buildHeaders(
					[
						'Content-Length' => strlen( $body ),
						'Content-Type'   => 'multipart/form-data; boundary=------WebKitFormBoundaryEDnegskZbO29yK7o',
						'Host'           => 'upload.facebook.com'
					]
				)
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$post = '';
		}

		preg_match( '/\"photoID\":\"([0-9]+)/i', $post, $photoId );

		return isset( $photoId[ 1 ] ) ? $photoId[ 1 ] : 0;
	}

	private function waterfallId ()
	{
		return md5( uniqid() . rand( 0, 99999999 ) . uniqid() );
	}

	private function getPrivacyX ()
	{
		$url = 'https://touch.facebook.com/privacy/timeline/saved_custom_audience_selector_dialog/?fb_dtsg=' . $this->fb_dtsg();

		try
		{
			$getData = (string) $this->client->request( 'GET', $url )->getBody();
		}
		catch ( Exception $e )
		{
			$getData = '';
		}

		preg_match( '/\:\"([0-9]+)\"/i', htmlspecialchars_decode( $getData ), $firstPrivacyX );

		return isset( $firstPrivacyX[ 1 ] ) ? $firstPrivacyX[ 1 ] : '0';
	}

	private function conertToMultipartArray ( $arr )
	{
		$newArr = [];

		foreach ( $arr as $name => $value )
		{
			if ( is_array( $value ) )
			{
				foreach ( $value as $name2 => $value2 )
				{
					$newArr[] = [
						'name'     => $name . '[' . $name2 . ']',
						'contents' => $value2
					];
				}
			}
			else
			{
				$newArr[] = [
					'name'     => $name,
					'contents' => $value
				];
			}
		}

		return $newArr;
	}

	private function parsePostRepsonse ( $response )
	{
		if ( empty( $response ) )
		{
			return [ FALSE, __("Error! Response is empty") ];
		}

		$hasError = preg_match( '/\,\"error\"\:([0-9]+)\,/iU', $response, $errCode );

		if ( $hasError && (int) $errCode[ 1 ] > 0 )
		{
			$errCode = (int) $errCode[ 1 ];

			preg_match( '/\,\"errorDescription\"\:\"(.+)\"\,/iU', $response, $errMsg );
			$errMsg = isset( $errMsg[ 1 ] ) ? $errMsg[ 1 ] : __("Error");

			return [ FALSE, __( $errMsg ) ];
		}

		return [ TRUE ];
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$result = [
			'error'     => TRUE,
			'message' => NULL
		];
		$myInfo = $this->myInfo();

		if ( $this->fbUserId === $myInfo[ 'id' ] )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function sendStory ( $accId, $message, $image, $nodeType, $link = '', $team_id = '' )
	{
		$imgForStory = self::imageForStory( $image, $message, $team_id );

		if ( empty( $imgForStory ) )
		{
			return [
				'status'    => 'error',
				'message' => __("The image resolution is too large")
			];
		}

		$uploadID = $this->uploadPhoto( $imgForStory[ 'path' ], $accId, $nodeType );

		unlink( $imgForStory[ 'path' ] );

		if ( empty( $uploadID ) )
		{
			return [
				'status'    => 'error',
				'message' => __("Failed to upload the image")
			];
		}

		$uuid = self::uuid();

		$isNewPageORAccount = ! empty( $this->newPageID ) || $nodeType === 'profile';

		if ( $isNewPageORAccount )
		{
			$variables = [
				'input' => [
					'actor_id'              => $accId,
					'attachments'           => [
						[
							'photo' => [
								'id'       => $uploadID,
								'overlays' => NULL
							]
						]
					],
					'audiences'             => [
						[
							'stories' => [
								'self' => [
									'target_id' => $accId
								]
							]
						]
					],
					'audiences_is_complete' => TRUE,
					'client_mutation_id'    => '1',
					'logging'               => [
						'composer_session_id' => $uuid
					],
					'navigation_data'       => [
						'attribution_id_v2' => 'StoriesCreateRoot.react,comet.stories.create,unexpected,1665383878113,546960,,;CometHomeRoot.react,comet.home,via_cold_start,1665383845813,595122,4748854339,'
					],
					'source'                => 'WWW',
					'tracking'              => [
						NULL
					]
				]
			];

			if ( ! empty( $link ) && $nodeType !== 'account' )
			{
				$variables[ 'input' ][ 'call_to_action_data' ] = [
					'is_cta_share_post' => TRUE,
					'link'              => $link,
					//'page' => $legatedID,
					'type'              => 'SEE_MORE'
				];
			}
		}
		else
		{
			$variables = [
				'input' => [
					'client_mutation_id' => '1',
					'base'               => [
						'actor_id'                        => $accId,
						'composer_entry_point'            => 'biz_web_content_manager_calendar_tab_stories',
						'source'                          => 'WWW',
						'unpublished_content_data'        => NULL,
						'attachments'                     => [
							[
								'photo' => [
									'id'                        => $uploadID,
									'story_call_to_action_data' => NULL
								]
							]
						],
						'story_original_attachments_data' => [
							[
								'original_photo_id' => $uploadID,
								'burned_photo'      => [
									'story_call_to_action_data' => empty( $link ) ? NULL : [
										'is_cta_share_post' => TRUE,
										'link'              => $link,
										'type'              => 'SEE_MORE',
										'link_title'        => 'See more'
									],
									'id'                        => $uploadID
								]
							]
						]
					],
					'channels'           => [
						'FACEBOOK_STORY'
					],
					'identities'         => [
						$accId
					],
					'logging'            => [
						'composer_session_id' => $uuid
					]
				]
			];
		}

		$sendData = [
			'fb_dtsg'                  => $this->fb_dtsg,
			'lsd'                      => $this->lsd,
			'__user'                   => empty( $this->newPageID ) ? $this->fbUserId : $accId,
			'av'                       => $accId,
			'__a'                      => '1',
			'dpr'                      => '2',
			'__ccg'                    => 'GOOD',
			'__comet_req'              => '15',
			'req'                      => '1c',
			'fb_api_caller_class'      => 'RelayModern',
			'fb_api_req_friendly_name' => $isNewPageORAccount ? 'StoriesCreateMutation' : 'BusinessComposerStoryCreationMutation',
			'server_timestamps'        => 'true',
			'doc_id'                   => $isNewPageORAccount ? '5731665720186663' : '5354681964593829',
			'variables'                => json_encode( $variables )
		];

		$sendData = http_build_query( $sendData, '', '&' );

		try
		{
			$post = $this->client->post( 'https://facebook.com/api/graphql/', [
				'headers' => $this->buildHeaders( [
					'X-FB-Friendly-Name' => 'ComposerStoryCreateMutation',
					'Content-Length'     => strlen( $sendData )
				] ),
				'body'    => $sendData,
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'message' => $e->getMessage()
			];
		}

		$story = json_decode( $post, TRUE );

		if ( empty( $story ) )
		{
			return [
				'status'    => 'error',
				'message' => fsp__("An error occured while sharing the story")
			];
		}

		if ( ! $isNewPageORAccount )
		{
			$parsedError = $this->parseErrors( $post );

			if ( $parsedError !== FALSE )
			{
				return [
					'status'    => 'error',
					'message' => $parsedError
				];
			}

			return [
				'status' => 'success',
				'id'     => $accId
			];
		}

		if ( empty( $story[ 'data' ][ 'story_create' ][ 'viewer' ][ 'actor' ][ 'story_bucket' ][ 'nodes' ][ 0 ][ 'first_story_to_show' ][ 'id' ] ) )
		{
			return [
				'status'    => 'error',
				'message' => 'An error occured while sharing the story.'
			];
		}

		$storyID = $story[ 'data' ][ 'story_create' ][ 'viewer' ][ 'actor' ][ 'story_bucket' ][ 'nodes' ][ 0 ][ 'first_story_to_show' ][ 'id' ];

		$storyID = base64_decode( $storyID );

		if ( empty( $storyID ) )
		{
			return [
				'status'    => 'error',
				'message' => 'An error occured while sharing the story.'
			];
		}

		$storyID = explode( ':', $storyID );

		if ( empty( $storyID[ 2 ] ) )
		{
			return [
				'status'    => 'error',
				'message' => 'Unknown error'
			];
		}

		return [
			'status' => 'ok',
			'id'     => $storyID[ 2 ]
		];
	}

	private static function imageForStory ( $photo_path, $title, $team_id )
	{
		$storyBackground    = get_team_data("fb_story_bg", "#636e72", $team_id);
		$titleBackground    = get_team_data("fb_story_title_bg", "#000000", $team_id);
		$titleBackgroundOpc = (int)get_team_data("fb_story_bg_opacity", 30, $team_id);
		$titleColor         = get_team_data("fb_story_title_color", "#FFFFFF", $team_id);
		$titleTop           = (int)get_team_data("fb_story_title_top", 125, $team_id);
		$titleLeft          = (int)get_team_data("fb_story_title_left", 30, $team_id);
		$titleWidth         = get_team_data("fb_story_title_width", 660, $team_id);
		$titleFontSize      = (int)get_team_data("fb_story_title_font_size", 30, $team_id);
		$titleFontFamily    = get_team_data("fb_story_title_font_family", "notosans", $team_id);
		$titleRtl           = (int)get_team_data("fb_story_title_text_direction", 1, $team_id);

		$storyBackground = str_replace("#", "", $storyBackground);
		$titleBackground = str_replace("#", "", $titleBackground);
		$titleColor = str_replace("#", "", $titleColor);

		if ( $titleRtl == 2 )
		{
			$p_a   = new \PersianStringDecorator();
			$title = $p_a->decorate( $title, FALSE, TRUE );
		}

		$titleBackgroundOpc = $titleBackgroundOpc > 100 || $titleBackgroundOpc < 0 ? 0.3 : $titleBackgroundOpc / 100;

		$storyBackground   = hexToRgb( $storyBackground );
		$storyBackground[] = 0;// opacity

		$storyW = 1080 / 1.5;
		$storyH = 1920 / 1.5;

		$imageInf    = new \PHPImage( $photo_path );
		$imageWidth  = $imageInf->getWidth();
		$imageHeight = $imageInf->getHeight();

		if ( $imageWidth * $imageHeight > 3400 * 3400 ) // large file
		{
			return NULL;
		}

		$imageInf->cleanup();
		unset( $imageInf );

		$w1 = $storyW;
		$h1 = ( $w1 / $imageWidth ) * $imageHeight;

		if ( $h1 > $storyH )
		{
			$w1 = ( $storyH / $h1 ) * $w1;
			$h1 = $storyH;
		}

		$image = new PHPImage();
		$image->initialiseCanvas( $storyW, $storyH, 'img', $storyBackground );

		$image->draw( $photo_path, '50%', '50%', $w1, $h1 );

		$titleLength  = mb_strlen( $title, 'UTF-8' );
		$titlePercent = $titleLength - 40;
		if ( $titlePercent < 0 )
		{
			$titlePercent = 0;
		}
		else if ( $titlePercent > 100 )
		{
			$titlePercent = 100;
		}

		// write title
		if ( ! empty( $title ) )
		{
			$textPadding = 10;
			$textWidth   = $titleWidth;
			$textHeight  = 100 + $titlePercent;
			$iX          = $titleLeft;
			$iY          = $titleTop;


			switch ($titleFontFamily) {
				case 'notosans':
					$image->setFont( __DIR__ . '/../Libraries/PHPImage/font/notosans.ttf' );
					break;

				case 'opensans':
					$image->setFont( __DIR__ . '/../Libraries/PHPImage/font/opensans.ttf' );
					break;

				case 'story':
					$image->setFont( __DIR__ . '/../Libraries/PHPImage/font/story.ttf' );
					break;
				
				default:
					$image->setFont( __DIR__ . '/../Libraries/PHPImage/font/arial.ttf' );
					break;
			}

			
			$image->rectangle( $iX, $iY, $textWidth + $textPadding, $textHeight - $textPadding, hexToRgb( $titleBackground ), $titleBackgroundOpc );

			$image->textBox( $title, [
				'fontSize'        => $titleFontSize,
				'fontColor'       => hexToRgb( $titleColor ),
				'x'               => $iX,
				'y'               => $iY,
				'strokeWidth'     => 1,
				'strokeColor'     => [ 99, 110, 114 ],
				'width'           => $textWidth,
				'height'          => $textHeight,
				'alignHorizontal' => 'center',
				'alignVertical'   => 'center'
			] );
		}

		$newFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid( 'fs_' );
		//static::moveToTrash( $newFileName );

		$image->setOutput( 'jpg' )->save( $newFileName );

		return [
			'width'  => $storyW,
			'height' => $storyH,
			'path'   => $newFileName
		];
	}

	private static function uuid ()
	{
		$uuid = md5( uniqid() );

		$return = substr( $uuid, 0, 8 ) . "-";
		$return .= substr( $uuid, 8, 4 ) . "-";
		$return .= substr( $uuid, 12, 4 ) . "-";
		$return .= substr( $uuid, 16, 4 ) . "-";
		$return .= substr( $uuid, 20, 10 );

		return $return;
	}
}
}