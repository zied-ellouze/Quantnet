<?php
/*
Plugin Name: Amazon Showcase
Plugin URI: http://www.aaronforgue.com/projects/amazon-showcase/
Description: A plugin for showcasing items from Amazon. Simply enter the ASIN/ISBN numbers of any products and optionally enter an Associate ID for earning commissions.
Author: Aaron Forgue
Version: 2.2
Author URI: http://www.aaronforgue.com
Author Note:
	TODO: Handle "windowing" and rotation preferences
	TODO: Feature to import a wishlist as a showcase
	TODO: Look into paging of Amazon API queries - should allow for 20 asins as opposed to just 10
*/

/**
 * Used to open URLs. Have to do some checks because of varying server settings
 *
 * @param string $url
 * @return string
 */
function getUrl($url) {

	// Use file_get_contents
	if (ini_get('allow_url_fopen') && function_exists('file_get_contents')) {
		return @file_get_contents($url);
	}

	// Use fopen
	if (ini_get('allow_url_fopen') && !function_exists('file_get_contents')) {
		if (false === $fh = fopen($url, 'rb', false)) {
			user_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}

		clearstatcache();
		if ($fsize = @filesize($url)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}

		fclose($fh);

		return $data;
	}

	// Use cURL
	if (function_exists('curl_init')) {
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, 15);
		$data = @curl_exec($c);
		curl_close($c);

		return $data;
	}

	return false;
}

class AmazonShowcase {
	var $_initiated = false;
	var $_options = array();

	var $_showcases = array();


	/**
	 * Initializes a new AmazonShowcase
	*/
	function AmazonShowcase() {
	}

	/**
	 * Generates a new showcase and adds it to the list
	 *
	 * @return string Identifier of newly created showcase
	 */
	function AddNewShowcase() {
		// Create new Showcase object
		$showcase = new AMZSCShowcase();
		$showcaseIdentifier = $showcase->GetIdentifier();

		// Add it to the list of existing showcases
		$this->_showcases[$showcaseIdentifier] = $showcase;

		$this->Save();

		return $showcaseIdentifier;
	}

	/**
	 * Creates a new showcase with default values and saves to the database.
	 * Outputs HTML to be appended to options panel
	 */
	function AjaxAddShowcase() {
		$showcaseIdentifier = $this->AddNewShowcase();

		die($this->_showcases[$showcaseIdentifier]->ShowcaseOptionsFormHtml());
	}

	/**
	 * Creates a new showcase item with default values and saves to the database.
	 * Outputs HTML to be appended to options panel
	 *
	 * @param string $showcaseIdentifier
	 */
	function AjaxAddShowcaseItem($showcaseIdentifier = null) {
		if (!$showcaseIdentifier) { return 0; }

		$itemIdentifier = $this->_showcases[$showcaseIdentifier]->AddItem();

		$this->Save();

		die($this->_showcases[$showcaseIdentifier]->ItemOptionsFormHtml($itemIdentifier));
	}

	/**
	 * Generates a preview of an Amazon Item and displays. Used for ajax calls
	 *
	 * @param mixed $asin
	 */
	function AjaxGenerateItemPreview($asin) {
		// Do some minor cleanup on the asin
		$asin = AMZSCShowcase::CleanAsin($asin);

		$amazonData = $GLOBALS['AMZSCAmazon']->GetItems(array($asin));
		
		if (isset($amazonData[$asin]['images']['thumbnail']['url'])) {
			die('<img src="'.$amazonData[$asin]['images']['thumbnail']['url'].'" height="'.$amazonData[$asin]['images']['thumbnail']['height'].'" width="'.$amazonData[$asin]['images']['thumbnail']['width'].'" alt="Image of: '.$amazonData[$asin]['title'].'" title="'.$amazonData[$asin]['title'].'" />');
		} else {
			die('Thumbnail preview not available. Sorry!');
		}
	}

	/**
	 * Removes a showcase completely.
	 *
	 * @param string $identifier
	 */
	function AjaxRemoveShowcase($identifier = null) {
		if (!$identifier) { return 0; }

		// Remove the showcase
		unset($this->_showcases[$identifier]);

		$this->Save();
	}

	/**
	 * Removes a showcase item completely.
	 *
	 * @param string $showcaseIdentifier
	 * @param string $itemIdentifier
	 */
	function AjaxRemoveShowcaseItem($showcaseIdentifier = null, $itemIdentifier= null) {
		if (!$showcaseIdentifier || !$itemIdentifier) { return 0; }

		// Remove the showcase item
		$this->_showcases[$showcaseIdentifier]->RemoveItem($itemIdentifier);

		$this->Save();
	}

	/**
	 * Displays the options page
	 */
	function DisplayOptionsPage() {
		$this->Initiate();

		if (!empty($_POST['amzshcs_form_action'])) {
			if ($_POST['amzshcs_form_action'] == 'update') {
				if (isset($_POST['amzshcs']['showcases'])) {
					foreach ($_POST['amzshcs']['showcases'] as $identifier => $showcase) {
						$this->_showcases[$identifier]->SetOptions($showcase);
						$this->_showcases[$identifier]->UpdateShowcaseAmazonData();
					}

					$this->Save();
				}
			} else if ($_POST['amzshcs_form_action'] == 'apiconfig') {
				$this->_AWSAccessKeyId = trim($_POST['amzshcs']['accesskeyid']);
				$this->_AWSSecretAccessKey = trim($_POST['amzshcs']['secretaccesskey']);

				$this->Save();
			}
		}

		$postUrl = $this->GetPostUrl();

		?>

		<script type="text/javascript">
			jQuery(document).ready( function() { add_postbox_toggles(); } );

			function add_postbox_toggles() {
				jQuery('.postbox h3').unbind();
				jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent()).toggleClass('closed'); } );
			}

			function amzshcs_addShowcase() {
				var url = '<?php echo $postUrl; ?>&amzshcs_ajax_action=addshowcase';

				jQuery('<div>Loading...</div>')
					.css('display', 'none')
					.appendTo('#amzshcs-showcases')
					.fadeIn('normal', function() {
						jQuery.get(url, function(data) {
							jQuery('#amzshcs-showcases div:last').fadeOut('normal', function() {
								jQuery(this).remove();

								jQuery(data)
									.css('display', 'none')
									.appendTo('#amzshcs-showcases')
									.fadeIn('normal', function() {
										add_postbox_toggles();
									});
							});
						});
					});
			}

			function amzshcs_removeShowcase(identifier) {
				var url = '<?php echo $postUrl; ?>&amzshcs_ajax_action=removeshowcase&amzshcs_identifier='+identifier;

				if (confirm('Are you sure you wish to remove this showcase? This action cannot be undone!')) {
					jQuery.get(url, function(data) {
						jQuery('#showcase-'+identifier).fadeOut('normal', function() {
							jQuery(this).remove();
						});
					});

				}
			}

			function amzshcs_addShowcaseItem(showcaseIdentifier) {
				var url = '<?php echo $postUrl; ?>&amzshcs_ajax_action=addshowcaseitem&amzshcs_showcase_identifier='+showcaseIdentifier;
				var container = '#showcase-'+showcaseIdentifier+'-items'

				jQuery('<tr><td>Loading...</td></tr>')
					.css('display', 'none')
					.appendTo(container)
					.fadeIn('normal', function() {
						jQuery.get(url, function(data) {
							jQuery(container+' tr:last').fadeOut('normal', function() {
								jQuery(this).remove();

								jQuery(data)
									.css('display', 'none')
									.appendTo(container)
									.fadeIn('normal');
							});
						});
					});
			}

			function amzshcs_removeShowcaseItem(showcaseIdentifier, itemIdentifier) {
				var url = '<?php echo $postUrl; ?>&amzshcs_ajax_action=removeshowcaseitem&amzshcs_showcase_identifier='+showcaseIdentifier+'&amzshcs_item_identifier='+itemIdentifier;


				if (confirm('Are you sure you wish to remove this item? This action cannot be undone!')) {
					jQuery.get(url, function(data) {
						jQuery('#showcase-'+showcaseIdentifier+'-item-'+itemIdentifier).fadeOut('normal', function() {
							jQuery(this).remove();
						});
					});

				}
			}

			function amzshcs_preview(showcaseIdentifier, itemIdentifier) {
				var asin = jQuery('#amzshcs-asin-'+showcaseIdentifier+'-'+itemIdentifier).val();
				var url = '<?php echo $postUrl; ?>&amzshcs_ajax_action=ajax_preview&amzshcs_asin='+asin;
				var previewId = '#amzshcs-preview-'+showcaseIdentifier+'-'+itemIdentifier;

				jQuery(previewId).fadeOut('normal', function() {
					jQuery(previewId)
						.empty()
						.append('Loading...')
						.fadeIn('normal', function() {
							jQuery.get(url, function(data) {
								jQuery(previewId).fadeOut('normal', function() {
									jQuery(previewId)
										.empty()
										.append(data)
										.fadeIn('normal');
								})
							});
						});
				});
			}
		</script>

		<style type="text/css">
			#amzshcs-showcases .inside { margin: 0 12px 12px; font-size: 11px; }

			h3.hndle span.toggle { color: #ccc; font-weight: normal; }

			#apiconfig { background: #fff; border: 1px solid #ccc; padding: 0 10px; width: 650px; }
			
			td.amzshcs-asin input { width: 175px; }
			td.amzshcs-asin ul { list-style-type: none; }
			td.amzshcs-asin ul li { border-right: 1px solid #999; display: inline; margin: 0 3px 0 0; padding: 0 6px 0 0; }
			td.amzshcs-asin ul li.amzshcs-asin-actions-last { border: 0; }
			td.amzshcs-imagesize select { width: 150px; }
			td.amzshcs-template textarea { height: 75px; width: 100%; }
		</style>

		<div class="wrap">
			<h3>Amazon Showcase Settings</h3>
			
			<form action="<?php echo $postUrl; ?>" id="apiconfig" method="post">
				
				<input type="hidden" name="amzshcs_form_action" value="apiconfig" />
				<input type="hidden" name="page_options" value="amzshcs" />
				
				<h4>Access Identifiers</h4>
				<p>The Amazon Product Advertising API is used to gather information on products. To make requests to this API, you must <a href="http://aws.amazon.com/" target="_blank">sign up for a free Amazon Web Services account</a> and then enter your access identifiers below. Access identifiers are used to authenticate requests to Product Advertising API.</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Access Key ID</th>
						<td><input type="text" name="amzshcs[accesskeyid]" value="<?php echo $this->_AWSAccessKeyId; ?>" style="width: 400px;" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Secret Access Key</th>
						<td><input type="text" name="amzshcs[secretaccesskey]" value="<?php echo $this->_AWSSecretAccessKey; ?>" style="width: 400px;" /></td>
					</tr>
				</table>

				<p class="submit">
					<input class="button-primary" type="submit" name="Submit" value="Save Changes" />
				</p>
			</form>
			
			<?php if (!empty($this->_AWSAccessKeyId) && !empty($this->_AWSSecretAccessKey)) { ?>
				<h4>Showcases</h4>
				<div id="amzshcs-showcases" class="metabox-holder">
				<?php
					if (!empty($this->_showcases)) {
						$postUrl = $this->GetPostUrl();
						foreach ($this->_showcases as $showcase) {
							echo $showcase->ShowcaseOptionsFormHtml($this->GetPostUrl());
						}
					} else {
						?><p id="amzshcs-welcome">It doesn't look like you've set up any showcases yet...</p><?php
					}
				?>
				</div>
				<p><a href="javascript:void(0);" onclick="amzshcs_addShowcase();"><?php _e('Add a new showcase', 'amazonshowcase'); ?></a></p>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Displays a showcase
	 *
	 * @param string $showcaseIdentifier
	 */
	function DisplayShowcase($showcaseIdentifier) {
		if (!empty($this->_showcases[$showcaseIdentifier])) {
			echo $this->_showcases[$showcaseIdentifier]->ShowcaseHTML();
		}
	}

	/**
	 * Enables Amazon Showcase and registers all appropriate WordPress hooks
	 */
	function Enable() {
		if (!isset($GLOBALS['AmazonShowcase'])) {

			$GLOBALS['AmazonShowcase'] = new AmazonShowcase();
			$GLOBALS['AmazonShowcase']->Initiate();

			// Hook for adding settings menus
			add_action('admin_menu', array(&$GLOBALS['AmazonShowcase'], 'RegisterOptionsPage'));

			// Filter for post content
			add_filter('the_content', array(&$GLOBALS['AmazonShowcase'], 'ParseContent'));
		}

	}

/*

	Check the item's cache:
	- If the cache is recent, use it
	- If the cache is out dated, query Amazon and update cache with results

	Forcing update
	- Query amazon and update cache with fresh data

	On preview - force cache update
	On showcase save - update entire showcase

	If missing data, attempt to query Amazon for fresh data


*/

	/**
	 * Utility function for determining the URL for posting forms to
	 *
	 * @return string
	 */
	function GetPostUrl() {
		$page = basename(__FILE__);
		if (!empty($_GET['page'])) {
			$page = preg_replace('[^a-zA-Z0-9\.\_\-]', '', $_GET['page']);
		}
		return $_SERVER['PHP_SELF'] . "?page=" .  $page;
	}

	/**
	 * Initiates the plugin and loads options
	 */
	function Initiate() {
		if (!$this->_initiated) {

			// Load Amazon Showcase data from the database
			$options = get_option('amzshcs');

			if ($options && is_array($options)) {
				
				$this->_AWSAccessKeyId = !empty($options['accesskeyid']) ? $options['accesskeyid'] : '';
				$this->_AWSSecretAccessKey = !empty($options['secretaccesskey']) ? $options['secretaccesskey'] : '';
	
				if (!empty($options['showcases'])) {
					foreach ($options['showcases'] as $showcaseIdentifier => $showcase) {
						$this->_showcases[$showcaseIdentifier] = new AMZSCShowcase($showcaseIdentifier);
					}
				}
			}
			
			$GLOBALS['AMZSCAmazon'] = new AMZSCAmazon($this->_AWSAccessKeyId, $this->_AWSSecretAccessKey);

			$this->_initiated = true;
		}
	}

	/**
	 * Parses post content looking for showcase tags. Replaces found showcase tags
	 * with showcase HTML
	 *
	 * @param string $content
	 * @return string
	 */
	function ParseContent($content = null) {
		$this->Initiate();

		if (!empty($this->_showcases)) {

			preg_match_all('/\[amazonshowcase_(.*?)\]/i', $content, $matches, PREG_PATTERN_ORDER);

			foreach ($matches[1] as $showcaseIdentifier) {
				if (isset($this->_showcases[$showcaseIdentifier])) {
					$content = str_replace('[amazonshowcase_'.$showcaseIdentifier.']', $this->_showcases[$showcaseIdentifier]->ShowcaseHTML(), $content);
				}
			}

		}

		return $content;
	}

	/**
	 * Adds the options page in the admin menu
	 */
	function RegisterOptionsPage() {
		if (function_exists('add_options_page')) {
			add_options_page(__('Amazon Showcase', 'amazonshowcase'), __('Amazon Showcase', 'amazonshowcase'), 'administrator', basename(__FILE__), array(&$this, 'DisplayOptionsPage'));
		}
	}

	/**
	 * Save Amazon Showcase data to the database
	 *
	 * @return bool
	 */
	function Save() {
		$options = array();
		
		$options['accesskeyid'] = $this->_AWSAccessKeyId;
		$options['secretaccesskey'] = $this->_AWSSecretAccessKey;

		foreach ($this->_showcases as $showcase) {
			$options['showcases'][$showcase->GetIdentifier()] = $showcase->GetOptionsArray();
		};

		return update_option("amzshcs", $options);
	}
}

class AMZSCAmazon {
	
	var $_AssociateId = 'amazonshowcase-20'; // Used if no associate ID is provided
	
	function AMZSCAmazon($accessKeyId = null, $secretAccessKey = null) {
		$this->_AWSAccessKeyId = $accessKeyId;
		$this->_AWSSecretAccessKey = $secretAccessKey;
	}

	/**
	 * Sends http request to Amazon web service and parses response into tidy array
	 *
	 * @param array $asins
	 * @param string $associateId
	 * @param string $locale
	 * @return array Items
	 */
	function GetItems($asins = array(), $associateId = null, $locale = 'us') {
	
		if (empty($this->_AWSAccessKeyId) || empty($this->_AWSSecretAccessKey)) {
			return false;
		}
		
		$items = array();

		// We batch the items into groups of 10 because that is the maxiumum numbers of ASINs
		// that can be queried at once.
		$asinBatches = array_chunk($asins, 10);

		foreach ($asinBatches as $asinBatch) {
			$xml = $this->ItemSearch($asinBatch, $associateId, $locale);
			
			if (!$xml || strpos($xml, 'SignatureDoesNotMatch') === true || strpos($xml, 'InvalidParameterValue')) {
				return false;
			}
			
			$itemData = $this->ParseXml($xml);
			foreach ($itemData as $asin => $data) { // Would use array_merge here, but numeric asins are reindexed
				$items[$asin] = $data;
			}
		}

		return $items;
	}

	/**
	 * Sends http request to Amazon web service
	 *
	 * @param array $asins
	 * @param string $associateId
	 * @param string $locale
	 * @return xml Amazon API Response
	 */
	function ItemSearch($asins = array(), $associateId = null, $locale = 'us') {

		if (empty($this->_AWSAccessKeyId) || empty($this->_AWSSecretAccessKey)) {
			return false;
		}
		
		if (is_array($asins) && !empty($asins)) {
			//Set the values for some of the parameters.
			
			$associateId = empty($associateId) ? $this->_AssociateId : $associateId;

			switch ($locale) {
				case 'uk': $base = 'ecs.amazonaws.co.uk'; break;
				case 'de': $base = 'ecs.amazonaws.de'; break;
				case 'jp': $base = 'ecs.amazonaws.jp'; break;
				case 'fr': $base = 'ecs.amazonaws.fr'; break;
				case 'ca': $base = 'ecs.amazonaws.ca'; break;
				default: $base = 'ecs.amazonaws.com'; break;
			}
			
			$uri = '/onca/xml';

			$queryString = 
				"AWSAccessKeyId=" . $this->_AWSAccessKeyId
				. "&AssociateTag=" . $associateId
				. "&ItemId=" . implode(',', $asins)
				. "&Operation=ItemLookup"
				. "&ResponseGroup=Small,Images"
				. "&Service=AWSECommerceService"
				. "&Timestamp=". gmdate("Y-m-d\TH:i:s\Z")
				. "&Version=2008-08-19";

			$queryString = str_replace(',', '%2C', $queryString);
		    $queryString = str_replace(':', '%3A', $queryString);
		
			// Build signature string
			$string_to_sign = "GET\n" . $base . "\n" . $uri . "\n" . $queryString;
			$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $this->_AWSSecretAccessKey, true));
					
			$request = 'http://' . $base . $uri . '?' . $queryString . '&Signature=' . urlencode($signature);

			//Catch the response in the $response object
			$response = getUrl($request);

			return $response;
		}

		return false;
	}


	/**
	 * Parses Amazon API response XML into tidy array
	 *
	 * @param string $xml
	 * @return array Items
	 */
	function ParseXml($xml) {
		$items = array();
		//echo "<pre>"; print_r($items); echo "</pre>"; die();
		preg_match_all("/<Item>(.*?)<\/Item>/", $xml, $itemMatches, PREG_SET_ORDER);

		foreach ($itemMatches as $itemMatch) {
			// ASIN
			preg_match("/<ASIN>(.*?)<\/ASIN>/", $itemMatch[1], $matches);
			$asin = $matches[1];

			// URL
			preg_match("/<DetailPageURL>(.*?)<\/DetailPageURL>/", $itemMatch[1], $matches);
			$url = $matches[1];

			// Title
			preg_match("/<Title>(.*?)<\/Title>/", $itemMatch[1], $matches);
			$title = $matches[1];

			// Authors
			$authors = array();
			$authorMatches = array();
			preg_match_all("/<(Author|Creator Role=\"Editor\")>(.*?)<\/(Author|Creator)>/", $itemMatch[1], $authorMatches, PREG_SET_ORDER);
			foreach ($authorMatches as $authorMatch) {
				$authors[] = $authorMatch[2];
			}

			// Images
			preg_match("/<ImageSet Category=\"primary\">(.*?)<\/ImageSet>/", $itemMatch[1], $imageSetMatches, PREG_OFFSET_CAPTURE);
			if (isset($imageSetMatches[1][0])) {
				$images = array();
				$imageMatches = array();
				preg_match_all("/<(\w+)Image>(.*?)<\/(\w+)Image>/", $imageSetMatches[1][0], $imageMatches, PREG_SET_ORDER);
				foreach ($imageMatches as $imageMatch) {
					if (!isset($images[strtolower($imageMatch[1])])) {
						// URL
						preg_match("/<URL>(.*?)<\/URL>/", $imageMatch[2], $matches);
						$imageUrl = $matches[1];

						// Height
						preg_match("/<Height Units=\"pixels\">(.*?)<\/Height>/", $imageMatch[2], $matches);
						$height = $matches[1];

						// Width
						preg_match("/<Width Units=\"pixels\">(.*?)<\/Width>/", $imageMatch[2], $matches);
						$width = $matches[1];

						$images[strtolower($imageMatch[1])] = array(
							'url' => $imageUrl,
							'height' => $height,
							'width' => $width
						);
					}
				}
			}


			$items[strtolower($asin)] =  array(
				'title' => $title,
				'author' => $authors,
				'url' => $url,
				'images' => $images
			);
		}

		return $items;
	}

}

class AMZSCShowcase {

	var $_identifier = '';
    var $_name = 'New Showcase';
    var $_locale = 'us';
    var $_associateId = '';
    var $_displayNum = 0;
    var $_sortMethod = 'normal';
	var $_items = array();
	var $_defaultItemTemplate = '<div class="amzshcs-item" id="amzshcs-item-[itemIdentifier]"> <a href="[url]">[image]</a> </div>';
	var $_defaultShowcaseTemplate = '<div class="amzshcs" id="amzshcs-[showcaseIdentifier]">[showcaseItems]</div>';

	/**
	 * Generates a new item and adds it to the showcase
	 *
	 */
	function AddItem() {
		$item = array(
			'identifier' => $this->GenerateIdentifier(),
			'asin' => '',
			'imageSize' => 'small',
			'template' => '',
		);

		$this->_items[$item['identifier']] = $item;

		return $item['identifier'];
	}

	/**
	 * Initializes a Showcase object
	 *
	 * @param string $showcaseIdentifier Identifier of the Showcase to be loaded
	 */
	function AMZSCShowcase($showcaseIdentifier = null) {
		if (!$showcaseIdentifier) {
			$this->_identifier = $this->GenerateIdentifier();
		} else {
			$this->_identifier = $showcaseIdentifier;
			$this->LoadOptions();
		}
	}

	/**
	 * Cleans up an ASIN in preparation for an API call
	 *
	 * @param string $asin ASIN to be cleaned
	 * @return string Cleaned ASIN
	 */
	function CleanAsin($asin) {
		$asin = str_replace('-', '', $asin);
		$asin = str_replace(' ', '', $asin);
		$asin = strtolower($asin);

		return $asin;
	}

	/**
	 * Generates a unique identifier
	 *
	 * @return string
	 */
	function GenerateIdentifier() {
		return md5(uniqid(rand(), true));
	}

	/**
	 * Return associate id
	 *
	 * @return string
	 */
	function GetAssociateId() {
		return $this->_associateId;
	}

	/**
	 * Return display num
	 *
	 * @return integer
	 */
	function GetDisplayNum() {
		return $this->_displayNum;
	}

	/**
	 * Return identifier
	 *
	 * @return string
	 */
	function GetIdentifier() {
		return $this->_identifier;
	}

	/**
	 * Return items
	 *
	 * @return array
	 */
	function GetItems() {
		return $this->_items;
	}

	/**
	 * Return locale
	 *
	 * @return string
	 */
	function GetLocale() {
		return $this->_locale;
	}

	/**
	 * Return name
	 *
	 * @return string
	 */
	function GetName() {
		return $this->_name;
	}

	/**
	 * Creates an array of all showcase options
	 *
	 */
	function GetOptionsArray() {
		$options = array(
			'associateid' => $this->_associateId,
			'displaynum' => $this->_displayNum,
			'identifier' => $this->_identifier,
			'items' => $this->_items,
			'locale' => $this->_locale,
			'name' => $this->_name,
			'sortmethod' => $this->_sortMethod
		);

		return $options;
	}

	/**
	 * Utility function for determining the URL for posting forms to
	 */
	function GetPostUrl() {
		$page = basename(__FILE__);
		if (isset($_GET['page']) && !empty($_GET['page'])) {
			$page = preg_replace('[^a-zA-Z0-9\.\_\-]', '', $_GET['page']);
		}
		return $_SERVER['PHP_SELF'] . "?page=" .  $page;
	}

	/**
	 * Return sort method
	 *
	 * @return string
	 */
	function GetSortMethod() {
		return $this->_sortMethod;
	}

	/**
	 * Generates options HTML for an individual showcase item
	 *
	 * @return string Generated HTML
	 */
	function ItemOptionsFormHtml($itemIdentifier = null) {
		if (isset($this->_items[$itemIdentifier])) {

			$item = $this->_items[$itemIdentifier];

			$selectedImageSize = array('swatch'=>'', 'small'=>'', 'tiny'=>'', 'medium'=>'', 'large'=>'');
			$selectedImageSize[$item['imageSize']] = 'selected';

			$template = !empty($item['template']) ? stripslashes($item['template']) : $this->_defaultItemTemplate;

			$preview = '';
			if (isset($item['amazonCache']['data']['images']['thumbnail']['url']) && !empty($item['amazonCache']['data']['images']['thumbnail']['url'])) {
				$preview = '<img src="'.$item['amazonCache']['data']['images']['thumbnail']['url'].'" height="'.$item['amazonCache']['data']['images']['thumbnail']['height'].'" width="'.$item['amazonCache']['data']['images']['thumbnail']['width'].'" />';
			}

			$html = <<<HTML
				<tr id="showcase-{$this->_identifier}-item-{$item['identifier']}" valign="top">
					<td class="amzshcs-asin">
						<input type="hidden" name="amzshcs[showcases][{$this->_identifier}][items][{$item['identifier']}][identifier]" value="{$item['identifier']}" />
						<input id="amzshcs-asin-{$this->_identifier}-{$item['identifier']}" type="text" name="amzshcs[showcases][{$this->_identifier}][items][{$item['identifier']}][asin]" value="{$item['asin']}" />
						<ul>
							<li><a href="#" onclick="amzshcs_preview('{$this->_identifier}', '{$item['identifier']}'); return false;">Preview</a></li>
							<li class="amzshcs-asin-actions-last"><a href="javascript:void(0);" onclick="amzshcs_removeShowcaseItem('{$this->_identifier}', '{$item['identifier']}');">Remove Item</a></li>
						</ul>
					</td>
					<td class="amzshcs-imagesize">
						<select name="amzshcs[showcases][{$this->_identifier}][items][{$item['identifier']}][imageSize]">
							<option value="swatch" {$selectedImageSize['swatch']}>Swatch (30px)</option>
							<option value="small" {$selectedImageSize['small']}>Small (75px)</option>
							<option value="tiny" {$selectedImageSize['tiny']}>Tiny (110px)</option>
							<option value="medium" {$selectedImageSize['medium']}>Medium (160px)</option>
							<option value="large" {$selectedImageSize['large']}>Large (450px+)</option>
						</select>
					</td>
					<td class="amzshcs-template"><textarea name="amzshcs[showcases][{$this->_identifier}][items][{$item['identifier']}][template]">{$template}</textarea></td>
					<td align="center" class="amzshcs-preview">
						<div id="amzshcs-preview-{$this->_identifier}-{$item['identifier']}">{$preview}</div>
					</td>
				</tr>

HTML;

			return $html;
		}

		return '';
	}

	/**
	 * Removes a specific item from the showcase
	 *
	 * @param string $itemIdentifier Identifier of item to be removed
	 */
	function RemoveItem($itemIdentifier = null) {
		if (isset($this->_items[$itemIdentifier])) {
			unset($this->_items[$itemIdentifier]);
		}
	}

	/**
	 * Assign all private variable values by load the showcase options from the DB
	 *
	 */
	function LoadOptions() {
		$options = get_option('amzshcs');

		if (isset($options['showcases'][$this->_identifier])) {
			$showcaseOptions = $options['showcases'][$this->_identifier];

			if (isset($showcaseOptions['associateid'])) {
				$this->_associateId = $showcaseOptions['associateid'];
			}

			if (isset($showcaseOptions['displaynum'])) {
				$this->_displayNum = $showcaseOptions['displaynum'];
			}

			if (isset($showcaseOptions['items'])) {
				$this->_items = $showcaseOptions['items'];
			}

			if (isset($showcaseOptions['locale'])) {
				$this->_locale = $showcaseOptions['locale'];
			}

			if (isset($showcaseOptions['name'])) {
				$this->_name = $showcaseOptions['name'];
			}

			if (isset($showcaseOptions['sortmethod'])) {
				$this->_sortMethod = $showcaseOptions['sortmethod'];
			}
		}
	}

	/**
	 * Set associate id
	 * @param string $associateId
	 * @return bool
	 */
	function SetAssociateId($associateId) {
		return $this->_associateId = $associateId;
	}

	/**
	 * Set display num
	 * @param integer $displayNum
	 * @return bool
	 */
	function SetDisplayNum($displayNum) {
		return $this->_displayNum = $displayNum;
	}

	/**
	 * Set identifier
	 * @param string $identifier
	 * @return bool
	 */
	function SetIdentifier($identifier) {
		return $this->_identifier = $identifier;
	}

	/**
	 * Set items
	 * @param array $items
	 * @return bool
	 */
	function SetItems($items) {
		return $this->_items = $items;
	}

	/**
	 * Set locale
	 * @param string $locale
	 * @return bool
	 */
	function SetLocale($locale) {
		return $this->_locale = $locale;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return bool
	 */
	function SetName($name) {
		return $this->_name = $name;
	}

	/**
	 * Set multiple object options at once by passing an array of option values
	 *
	 * @param array $options
	 */
	function SetOptions($options = array()) {
		foreach ($options as $option => $value) {

			switch (strtolower($option)) {
				case 'associateid':
					$this->_associateId = trim($value);
					break;
				case 'displaynum':
					$this->_displayNum = $value;
					break;
				case 'identifier':
					$this->_identifier = $value;
					break;
				case 'items':
					if (is_array($value)) {
						foreach ($value as $identifier => $item) {
							if (isset($item['asin'])) {
								$value[$identifier]['asin'] = $this->CleanAsin($item['asin']);
							}
						}

						$this->_items = $value;
					} else {
						$this->_items = array();
					}
					break;
				case 'locale':
					$this->_locale = $value;
					break;
				case 'name':
					$this->_name = $value;
					break;
				case 'sortmethod':
					$this->_sortMethod = $value;
					break;
			}
		}
	}

	/**
	 * Set sort method
	 * @param string $sortMethod
	 * @return bool
	 */
	function SetSortMethod($sortMethod) {
		return $this->_sortMethod = $sortMethod;
	}

	/**
	 * Returns the HTML for a showcase
	 *
	 * @param bool $encodeEntities If true, applies htmlspecialchars to data. Default false.
	 */
	function ShowcaseHtml($encodeEntities = false) {

		$items = $this->_items;

		if ($this->_displayNum > 0) {
			shuffle($items);
			$items = array_slice($items, 0, $this->_displayNum);
		}

		$itemHtml = '';
		foreach ($items as $item) {
			$itemIdentifier = $item['identifier'];

			if (!empty($item['template'])) {
				$itemHtml[$itemIdentifier] = stripslashes($item['template']);
			} else {
				$itemHtml[$itemIdentifier] = $this->_defaultItemTemplate;
			}

			if (is_array($item['amazonCache']['data']['author'])) {
				$authors = implode(', ', $item['amazonCache']['data']['author']);
			} else {
				$authors = $item['amazonCache']['data']['author'];
			}

			if ($encodeEntities) {
				$authors = htmlspecialchars($authors);
				$item['amazonCache']['data']['title'] = htmlspecialchars($item['amazonCache']['data']['title']);
			}

			$itemHtml[$itemIdentifier] = str_replace('[itemIdentifier]', $itemIdentifier, $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[author]', $authors, $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[title]', $item['amazonCache']['data']['title'], $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[url]', $item['amazonCache']['data']['url'], $itemHtml[$itemIdentifier]);

			$image = '';
			$image_url = '';
			$image_width = '';
			$image_height = '';

			if (!empty($item['amazonCache']['data']['images'][$item['imageSize']])) {
				$image_url = $item['amazonCache']['data']['images'][$item['imageSize']]['url'];
				$image_width = $item['amazonCache']['data']['images'][$item['imageSize']]['width'];
				$image_height = $item['amazonCache']['data']['images'][$item['imageSize']]['height'];

				$image = '<img src="'.$image_url.'" height="'.$image_height.'" width="'.$image_width.'" alt="Image of '.$item['amazonCache']['data']['title'].'" title="'.$item['amazonCache']['data']['title'].'" />';
			}

			$itemHtml[$itemIdentifier] = str_replace('[image]', $image, $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[image_url]', $image_url, $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[image_width]', $image_width, $itemHtml[$itemIdentifier]);
			$itemHtml[$itemIdentifier] = str_replace('[image_height]', $image_height, $itemHtml[$itemIdentifier]);
		}

		$html = $this->_defaultShowcaseTemplate;
		$html = str_replace('[showcaseIdentifier]', $this->_identifier, $html);
		$html = str_replace('[showcaseItems]', implode('', $itemHtml), $html);

		return $html;
	}

	/**
	 * Generates options HTML for the showcase
	 *
	 * @return string Generated HTML
	 */
	function ShowcaseOptionsFormHtml($postUrl = null) {

		if (!$postUrl) {
			$postUrl = $this->GetPostUrl();
		}

		$selectedLocale = array('us'=>'', 'uk'=>'', 'de'=>'', 'jp'=>'', 'fr'=>'', 'ca'=>'');
		$selectedLocale[$this->_locale] = 'selected';

		$selectedDisplayNum = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'');
		$selectedDisplayNum[$this->_displayNum] = 'selected';

		$selectedSortMethod = array('normal'=>'', 'random'=>'');
		$selectedSortMethod[$this->_sortMethod] = 'selected';

		$html = <<<HTML
		<div id="showcase-{$this->_identifier}" class="postbox closed">
			<h3 class="hndle"><span>{$this->_name}</span> <span class="toggle">click to toggle options</span></h3>
			<div class="inside">
				<form method="post" action="{$postUrl}">

					<input type="hidden" name="amzshcs_form_action" value="update" />
					<input type="hidden" name="page_options" value="amzshcs" />

					<p>You have two options for placing this showcase on your site:</p>
					<ol style="list-style-type: decimal; margin: 0 0 0 15px; padding: 0 0 0 5px;">
						<li><em>Place it in your posts or pages using:</em><br /><input readonly="true" size="65" type="text" value="[amazonshowcase_{$this->_identifier}]" /></li>
						<li><em>Place it in your templates using:</em><br /><input readonly="true" size="65" type="text" value="&lt;?php amazonshowcase('{$this->_identifier}'); ?&gt;" /></li>
					</ol>
					<h4>Showcase Options</h4>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">Identifier</th>
							<td><em>{$this->_identifier}</em><input type="hidden" name="amzshcs[showcases][{$this->_identifier}][identifier]" value="{$this->_identifier}" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">Name</th>
							<td><input type="text" name="amzshcs[showcases][{$this->_identifier}][name]" value="{$this->_name}" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">Locale</th>
							<td>
								<select name="amzshcs[showcases][{$this->_identifier}][locale]">
									<option value="us" {$selectedLocale['us']}>Amazon.com (US)</option>
									<option value="uk" {$selectedLocale['uk']}>Amazon.co.uk (UK)</option>
									<option value="de" {$selectedLocale['de']}>Amazon.de (DE)</option>
									<option value="jp" {$selectedLocale['jp']}>Amazon.co.jp (JP)</option>
									<option value="fr" {$selectedLocale['fr']}>Amazon.fr (FR)</option>
									<option value="ca" {$selectedLocale['ca']}>Amazon.ca (CA)</option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Associate ID (optional)</th>
							<td><input type="text" name="amzshcs[showcases][{$this->_identifier}][associateId]" value="{$this->_associateId}" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">Display slots</th>
							<td>
								<select name="amzshcs[showcases][{$this->_identifier}][displaynum]">
									<option value="0" {$selectedDisplayNum[0]}>All</option>
									<option value="10" {$selectedDisplayNum[10]}>10</option>
									<option value="9" {$selectedDisplayNum[9]}>9</option>
									<option value="8" {$selectedDisplayNum[8]}>8</option>
									<option value="7" {$selectedDisplayNum[7]}>7</option>
									<option value="6" {$selectedDisplayNum[6]}>6</option>
									<option value="5" {$selectedDisplayNum[5]}>5</option>
									<option value="4" {$selectedDisplayNum[4]}>4</option>
									<option value="3" {$selectedDisplayNum[3]}>3</option>
									<option value="2" {$selectedDisplayNum[2]}>2</option>
									<option value="1" {$selectedDisplayNum[1]}>1</option>
								</select><br/>
								<span>The number of items to display simultaneously. If you define more items than slots, they will be rotated through the slots based on the sort order.</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Item sort order</th>
							<td>
								<select name="amzshcs[showcases][{$this->_identifier}][sortmethod]">
									<option value="random" {$selectedSortMethod['random']}>Randomized</option>
								</select>
							</td>
						</tr>
					</table>
					<h4>Showcase Items</h4>
					<table class="widefat post fixed">
						<thead>
							<tr valign="top">
								<th scope="col" width="180">ASIN (or ISBN-10)</th>
								<th scope="col" width="160">Image Size</th>
								<th scope="col">Template (Advanced users only)<br /><small>Available Tags: [title] [author] [url] [image] [image_url] [image_width] [image_height]</small></th>
								<th scope="col" width="120">Thumbnail Preview</th>
							</tr>
						</thead>
						<tbody id="showcase-{$this->_identifier}-items">
HTML;

		if (!empty($this->_items)) {
			foreach ($this->_items as $itemIdentifier => $item) {
				$html .= $this->ItemOptionsFormHtml($itemIdentifier);
			}
		}

		$html .= <<<HTML
						</tbody>
					</table>

					<p><a href="javascript:void(0);" onclick="amzshcs_addShowcaseItem('{$this->_identifier}');">Add New Item</a></p>

					<p class="submit">
						<input class="button-primary" type="submit" name="Submit" value="Save Changes" />
						<input type="button" value="Remove This Showcase" onclick="amzshcs_removeShowcase('{$this->_identifier}'); return false;" />
					</p>
				</form>
			</div>
		</div>

HTML;

		return $html;
	}

	/**
	 * Update Amazon data for all showcase items
	 *
	 */
	function UpdateShowcaseAmazonData() {

		// Extact all of the ASINs from the items array
		$asins = array();
		foreach ($this->_items as $item) {
			$asins[] = $item['asin'];
		}

		if (!empty($asins)) {
			// Query Amazon for data
			$amazonData = $GLOBALS['AMZSCAmazon']->GetItems($asins, $this->_associateId, $this->_locale);

			foreach ($this->_items as $identifier => $item) {
				if (!empty($amazonData[$item['asin']])) {
					$this->_items[$identifier]['amazonCache'] = array(
						'lastUpdated' => date('Y-m-d H:i:s'),
						'data' => $amazonData[$item['asin']]
					);
				}
			}
		}
	}
}

class AMZSCWidget extends WP_Widget {
	/** constructor */
	function AMZSCWidget() {
		parent::WP_Widget(false, $name = 'Amazon Showcase');
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		extract($args);
		if (!empty($instance['showcase_id'])) {
			$title = apply_filters('widget_title', $GLOBALS['AmazonShowcase']->_showcases[$instance['showcase_id']]->_name);
			?>
				<?php echo $before_widget; ?>
				<?php if ( $title ) echo $before_title . $title . $after_title; ?>
				<?php echo amazonshowcase($instance['showcase_id']); ?>
				<?php echo $after_widget; ?>
			<?php
		}
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		
		$showcases = $GLOBALS['AmazonShowcase']->_showcases;
		
		if (!empty($showcases)) {
		?>
			<p>
				<label for="<?php echo $this->get_field_id('showcase_id'); ?>">
					<select id="<?php echo $this->get_field_id('showcase_id'); ?>" name="<?php echo $this->get_field_name('showcase_id'); ?>">
						<option value="">Choose a Showcase:&nbsp;&nbsp;</option>
						<?php foreach ($showcases as $showcaseIdentifier => $showcase) { ?>
							<option value="<?php echo $showcaseIdentifier; ?>" <?php echo ($instance['showcase_id'] == $showcaseIdentifier ? 'selected' : ''); ?>><?php echo $showcase->_name; ?>&nbsp;&nbsp;</option>
						<?php } ?>
					</select>
				</label>
			</p>
		<?php
		} else {
		?>
			<p>You have not created any showcases yet!</p>
			<p>You can create showcases in <a href="options-general.php?page=amazonshowcase.php">Settings > Amazon Showcase</a>.</p>
		<?php
		}
	}

}

/**
 * Display a showcase anywhere in a theme.
 *
 * @param string $showcaseIdentifier Showcase to display
 */
function amazonshowcase($showcaseIdentifier) {
	$GLOBALS['AmazonShowcase']->DisplayShowcase($showcaseIdentifier);
}


if (!ini_get('allow_url_fopen') && !function_exists('curl_init')) {
	echo '<p>Amazon Showcase is unable to function within your current server settings. Please either set "allow_url_fopen" to 1, or enable cURL.</p>';
} else if (!function_exists('http_build_query')) {
	echo '<p>Amazon Showcase is unable to function with your current version of PHP. Please upgrade to PHP 5 or greater.</p>';
} else if (!function_exists('hash_hmac') || base64_encode(hash_hmac("sha256", 'hashCheck', 'hashCheck', true)) != 'bCrWmap2WxEyY8MdKLibMiZoi8U/jZ1RftMm1ju0jxU=') {
	echo '<p>Amazon Showcase requires access to hash_hmac()/sha256.</p>';
} else {
	// Handle all ajax requests
	if (isset($_REQUEST["amzshcs_ajax_action"])) {
		AmazonShowcase::Enable();
		switch ($_REQUEST['amzshcs_ajax_action']) {
			case 'ajax_preview':
				$GLOBALS['AmazonShowcase']->AjaxGenerateItemPreview($_REQUEST["amzshcs_asin"]);
				break;
			case 'addshowcase':
				$GLOBALS['AmazonShowcase']->AjaxAddShowcase();
				break;
			case 'removeshowcase':
				$GLOBALS['AmazonShowcase']->AjaxRemoveShowcase($_REQUEST["amzshcs_identifier"]);
				break;
			case 'addshowcaseitem':
				$GLOBALS['AmazonShowcase']->AjaxAddShowcaseItem($_REQUEST["amzshcs_showcase_identifier"]);
				break;
			case 'removeshowcaseitem':
				$GLOBALS['AmazonShowcase']->AjaxRemoveShowcaseItem($_REQUEST["amzshcs_showcase_identifier"], $_REQUEST["amzshcs_item_identifier"]);
				break;
		}
	} else {
		// If Wordpress was initialized correctly, go ahead and enable the plugin
		if (defined('ABSPATH') && defined('WPINC')) {
			add_action('init', array('AmazonShowcase', 'Enable'), 1000, 0);
			add_action('widgets_init', create_function('', 'return register_widget("AMZSCWidget");'));
		}
	}
}


?>
