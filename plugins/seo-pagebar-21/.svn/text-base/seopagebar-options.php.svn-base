<?php
/*
SEO-Pagebar
Oliver Bockelmann und Sebastian Schmiedel(Flexib Webcoding)
*/

### Variables Variables Variables
$base_name = plugin_basename('seopagebar/seopagebar-options.php');
$base_page = 'admin.php?page='.$base_name;
$mode = trim($_GET['mode']);
$seopagebar_settings = array('seopagebar_options');

### Form Processing 
if(!empty($_POST['do'])) {
	// Decide What To Do
	switch($_POST['do']) {
		// Save changes (Einstellungen speichern)
		case __('Einstellungen speichern', 'seopagebar'):
			$seopagebar_options = array();
			$seopagebar_options['ueber_akt'] = addslashes($_POST['ueber_akt']);
			$seopagebar_options['ueber'] = addslashes($_POST['ueber']);
			$seopagebar_options['ueber_link'] = addslashes($_POST['ueber_link']);
			$seopagebar_options['ueber_tag'] = addslashes($_POST['ueber_tag']);
			$seopagebar_options['ueber_class'] = addslashes($_POST['ueber_class']);
			$seopagebar_options['ueber_follow'] = addslashes($_POST['ueber_follow']);
			$seopagebar_options['ueber_full'] = addslashes($_POST['ueber_full']);		
			$seopagebar_options['unter_akt'] = addslashes($_POST['unter_akt']);
			$seopagebar_options['unter_aut'] = addslashes($_POST['unter_aut']);
			$seopagebar_options['unter'] = addslashes($_POST['unter']);
			$seopagebar_options['unter_tag'] = addslashes($_POST['unter_tag']);
			$seopagebar_options['unter_class'] = addslashes($_POST['unter_class']);
			$seopagebar_options['unter_full'] = addslashes($_POST['unter_full']);		
			$seopagebar_options['next'] = addslashes($_POST['next']);
			$seopagebar_options['previous'] = addslashes($_POST['previous']);
			$seopagebar_options['zwischen'] = addslashes($_POST['zwischen']);
			$seopagebar_options['css_class'] = addslashes($_POST['css_class']);			
			$seopagebar_options['css_class_link'] = addslashes($_POST['css_class_link']);
			$seopagebar_options['nofollow'] = addslashes($_POST['nofollow']);
			$seopagebar_options['mouse'] = addslashes($_POST['mouse']);
			$seopagebar_options['mouse_over_text'] = addslashes($_POST['mouse_over_text']);
			$seopagebar_options['mouse_auto'] = addslashes($_POST['mouse_auto']);
			$seopagebar_options['anzeige_leer'] = addslashes($_POST['anzeige_leer']);
			$seopagebar_options['seiten'] = addslashes($_POST['seiten']);			
			$seopagebar_options['sprache'] = addslashes($_POST['sprache']);		
			
			$update_seopagebar_queries = array();
			$update_seopagebar_text = array();
			$update_seopagebar_queries[] = update_option('seopagebar_options', $seopagebar_options);
			$update_seopagebar_text[] = __('SEO Pagebar Einstellungen', 'seopagebar');
			$i=0;
			$text = '';
			foreach($update_seopagebar_queries as $update_seopagebar_query) {
				if($update_seopagebar_query) {
					$text .= '<font color="green">'.$update_seopagebar_text[$i].' '.__('Die neuen Einstellungen wurden &uuml;bernommen!', 'seopagebar').'</font><br />';
				}
				$i++;
			}
			if(empty($text)) {
				$text = '<font color="red">'.__('Die neuen Einstellungen wurden nicht &uuml;bernommen!', 'seopagebar').'</font>';
			}
			
		break;
		
		// Reload Original Settings (Standardeinstellungen wiederherstellen)
		case __('Standardeinstellungen wiederherstellen', 'seopagebar'):
			$seopagebar_options = array();		
			$seopagebar_options['ueber_akt'] = '1';
			$seopagebar_options['ueber'] = 'Im Blog st&ouml;bern';
			$seopagebar_options['ueber_link'] = '/';
			$seopagebar_options['ueber_tag'] = 'h2';
			$seopagebar_options['ueber_class'] = '';
			$seopagebar_options['ueber_follow'] = '0';
			$seopagebar_options['ueber_full'] = '';
			$seopagebar_options['unter_akt'] = '1';
			$seopagebar_options['unter_aut'] = '1';
			$seopagebar_options['unter'] = '';
			$seopagebar_options['unter_tag'] = 'p';
			$seopagebar_options['unter_class'] = '';
			$seopagebar_options['unter_full'] = '';	
			$seopagebar_options['next'] = '&raquo;';
			$seopagebar_options['previous'] = '&laquo;';
			$seopagebar_options['zwischen'] = '...';
			$seopagebar_options['css_class'] = 'seopagebar';		
			$seopagebar_options['css_class_link'] = '';
			$seopagebar_options['nofollow'] = '0';
			$seopagebar_options['mouse'] = '1';
			$seopagebar_options['mouse_over_text'] = 'Blogartikel';
			$seopagebar_options['mouse_auto'] = '1';
			$seopagebar_options['anzeige_leer'] = '0';
			$seopagebar_options['seiten'] = '7';	
			$seopagebar_options['sprache'] = 'de_DE';
			$update_seopagebar_queries = array();
			$update_seopagebar_queries[] = update_option('seopagebar_options', $seopagebar_options);

			if($update_seopagebar_queries) {
				$text .= '<font color="green">'.$update_seopagebar_text[$i].' '.__('Die Standardeinstellungen wurden wiederhergestellt!', 'seopagebar').'</font><br />';
			}
			else {
				$text = '<font color="red">'.__('Die Standardeinstellungen konnten nicht wiederhergestellt werden!', 'seopagebar').'</font>';	
			}

		break;

		// Uninstall (Deinstallieren) 
		case __('SEO Pagebar deinstallieren', 'seopagebar') :
			if(trim($_POST['uninstall_seopagebar']) == '1') {
				echo '<div id="message" class="updated fade">';
				echo '<p>';
				foreach($seopagebar_settings as $setting) {
					$delete_setting = delete_option($setting);
					if($delete_setting) {
						echo '<font color="green">';
						printf(__('\'%s\' wurden gel&ouml;scht.', 'seopagebar'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					} else {
						echo '<font color="red">';
						printf(__('Fehler beim l&ouml;schen der \'%s\'.', 'seopagebar'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					}
				}
				echo '</p>';
				echo '</div>'; 
				$mode = 'end-UNINSTALL';
			}
			break;

	}
}


if(!empty($text)) { echo '<div id="message" class="updated fade"><p>'.$text.'</p></div>'; }

switch($mode) {
	// Uninstall Seo Pagebar (SEO Pagebar deinstallieren) 
	case 'end-UNINSTALL':
		$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=seopagebar/seopagebar.php';

		if(function_exists('wp_nonce_url')) { 
			$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_seopagebar/seopagebar.php');
		}
		echo '<div class="wrap">';
		echo '<h2>'.__('SEO Pagebar deinstallieren', 'seopagebar').'</h2>';
		echo '<p><strong>'.sprintf(__('<a href="%s">Klick hier</a> um die Deinstallation der SEO Pagebar zu beenden und das Plugin zu deaktivieren.', 'seopagebar'), $deactivate_url).'</strong></p>';
		echo '</div>';
	break;
	
	// Main Formular (Hauptformular)
	default:
		$seopagebar_options = get_option('seopagebar_options'); ?>

		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
		<div class="wrap"> 
			<h2><?php _e('SEO Pagebar Einstellungen', 'seopagebar'); ?></h2><br /> 
			<div align="left">
				<input type="submit" name="do" class="button" value="<?php _e('Einstellungen speichern', 'seopagebar'); ?>" />&nbsp;&nbsp;<input type="submit" name="do" class="button" value="<?php _e('Standardeinstellungen wiederherstellen', 'seopagebar'); ?>" />
			</div><br />
			<fieldset class="options">
				<legend><?php _e('SEO Pagebar Einstellungen', 'seopagebar'); ?></legend>
				<table class="form-table">
					<tr> 
						<th style="width: 250px"><?php _e('Sprache', 'seopagebar'); ?></th>
						<td align="left">
							<select name="sprache" size="1">
								<option value="de_DE"<?php if($seopagebar_options['sprache']=="de_DE") echo " selected='selected'"; ?>  ><?php _e('Deutsch', 'seopagebar'); ?></option>
								<option value="en_EN"<?php if($seopagebar_options['sprache']=="en_EN") echo " selected='selected'"; ?>><?php _e('Englisch', 'seopagebar'); ?></option>
								<option value="es_ES"<?php if($seopagebar_options['sprache']=="es_ES") echo " selected='selected'"; ?>><?php _e('Spanisch', 'seopagebar'); ?></option>
							</select>	
							<a href="<?php echo $_SERVER['REQUEST_URI']; ?>" title="<?php _e('Aktualisieren', 'seopagebar'); ?>"><?php _e('Aktualisieren', 'seopagebar'); ?></a>			
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Text f&uuml;r vorw&auml;rts bl&auml;ttern', 'seopagebar'); ?></th>
						<td>
							<input type="text" name="next" value="<?php echo stripslashes($seopagebar_options['next']); ?>" size="50" />					
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Text f&uuml;r r&uuml;ckw&auml;rts bl&auml;ttern', 'seopagebar'); ?></th>
						<td>
							<input type="text" name="previous" value="<?php echo stripslashes($seopagebar_options['previous']); ?>" size="50" />					
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Leerr&auml;ume zwischen Pagebar-Elementen', 'seopagebar'); ?></th>
						<td><input type="text" name="zwischen" value="<?php echo stripslashes($seopagebar_options['zwischen']); ?>" size="50" /></td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('CSS-Klasse f&uuml;r SEO Pagebar', 'seopagebar'); ?></th>
						<td>class="<input type="text" name="css_class" value="<?php echo stripslashes($seopagebar_options['css_class']); ?>" size="20" />"</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('CSS-Klasse f&uuml;r Links', 'seopagebar'); ?></th>
						<td>class="<input type="text" name="css_class_link" value="<?php echo stripslashes($seopagebar_options['css_class_link']); ?>" size="20" />"</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Links mit Nofollow-Tag?', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="nofollow" value="1" <?php if ($seopagebar_options['nofollow']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="nofollow" value="0" <?php if ($seopagebar_options['nofollow']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Mouseover bei Links', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="mouse" value="1" <?php if ($seopagebar_options['mouse']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="mouse" value="0" <?php if ($seopagebar_options['mouse']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Mouseovertext', 'seopagebar'); ?></th>
						<td align="left">title="<input type="text" name="mouse_over_text" value="<?php echo stripslashes($seopagebar_options['mouse_over_text']); ?>" size="40" />"</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Mouseover Text automatisch setzen mit Kategorie/Tag/Suchwort', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="mouse_auto" value="1" <?php if ($seopagebar_options['mouse_auto']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="mouse_auto" value="0" <?php if ($seopagebar_options['mouse_auto']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?><br />
							<small><?php _e('wenn ausgef&uuml;llt, wird eigener Mouseovertext ignoriert', 'seopagebar'); ?></small>
						</td> 
					</tr>			
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Soll die SEO Pagebar angezeigt werden, wenn es nur eine Seite gibt?', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="anzeige_leer" value="1" <?php if ($seopagebar_options['anzeige_leer']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="anzeige_leer" value="0" <?php if ($seopagebar_options['anzeige_leer']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Wieviele Seiten sollen in der Mitte angezeigt werden?', 'seopagebar'); ?></th>
						<td><input type="text" name="seiten" value="<?php echo stripslashes($seopagebar_options['seiten']); ?>" size="10" /></td> 
					</tr>
				</table>
			</fieldset>
			<br />
			<fieldset class="options">
				<legend><?php _e('Seoeinstellungen - &Uuml;berschrift der SEO Pagebar', 'seopagebar'); ?></legend>
				<table class="form-table">
					<tr valign="top"> 
						<th astyle="width: 250px"><?php _e('Soll eine &Uuml;berschrift angezeigt werden?', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="ueber_akt" value="1" <?php if ($seopagebar_options['ueber_akt']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="ueber_akt" value="0" <?php if ($seopagebar_options['ueber_akt']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('&Uuml;berschrift', 'seopagebar'); ?></th>
						<td align="left"><input type="text" name="ueber" value="<?php echo stripslashes($seopagebar_options['ueber']); ?>" size="50" /></td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('&Uuml;berschrift verlinkt zu', 'seopagebar'); ?></th>
						<td>&lt;a href="<input type="text" name="ueber_link" value="<?php echo stripslashes($seopagebar_options['ueber_link']); ?>" size="50" /> "/&gt;</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('HTML-Tag um die &Uuml;berschrift', 'seopagebar'); ?></th>
						<td align="left">
							<select name="ueber_tag" size="1">
								<option value="h1"<?php if($seopagebar_options['ueber_tag']=="h1") echo " selected='selected'"; ?>>h1</option>
								<option value="h2"<?php if($seopagebar_options['ueber_tag']=="h2") echo " selected='selected'"; ?>>h2</option>
								<option value="h3"<?php if($seopagebar_options['ueber_tag']=="h3") echo " selected='selected'"; ?>>h3</option>
								<option value="h4"<?php if($seopagebar_options['ueber_tag']=="h4") echo " selected='selected'"; ?>>h4</option>
								<option value="h5"<?php if($seopagebar_options['ueber_tag']=="h5") echo " selected='selected'"; ?>>h5</option>
								<option value="h6"<?php if($seopagebar_options['ueber_tag']=="h6") echo " selected='selected'"; ?>>h6</option>
								<option value="p"<?php if($seopagebar_options['ueber_tag']=="p") echo " selected='selected'"; ?>>p</option>						
							</select>
							class="<input type="text" name="ueber_class" value="<?php echo stripslashes($seopagebar_options['ueber_class']); ?>" size="20" />"				
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Link mit Nofollow Tag?', 'seopagebar'); ?></th>
						<td align="left">
							<input type="radio" name="ueber_follow" value="1" <?php if ($seopagebar_options['ueber_follow']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="ueber_follow" value="0" <?php if ($seopagebar_options['ueber_follow']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr>
						<td colspan="2" style="border-top: 1px solid #C0C0C0;"></td>
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Oder ganze Zeile eingeben', 'seopagebar'); ?></th>
						<td align="left">
							<input type="text" name="ueber_full" value="<?php echo stripslashes($seopagebar_options['ueber_full']); ?>" size="50" /><br />
							<small><?php _e('wenn ausgef&uuml;llt, wird der Rest ignoriert', 'seopagebar'); ?></small>										
						</td> 
					</tr>
				</table>
			</fieldset>
			<br />
			<fieldset class="options">
				<legend><?php _e('Seoeinstellungen - Untertitel der SEO Pagebar', 'seopagebar'); ?></legend>
				<table class="form-table">
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Soll ein Untertitel angezeigt werden?', 'seopagebar'); ?></th>
						<td align="left">
							<input type="radio" name="unter_akt" value="1" <?php if ($seopagebar_options['unter_akt']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="unter_akt" value="0" <?php if ($seopagebar_options['unter_akt']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Soll automatisch die Kategorie, das Suchwort oder Tag gesetzt werden?', 'seopagebar'); ?></th>
						<td>
							<input type="radio" name="unter_aut" value="1" <?php if ($seopagebar_options['unter_aut']==1) echo "checked='checked'"; ?> /> <?php _e('Ja', 'seopagebar'); ?>
							<input type="radio" name="unter_aut" value="0" <?php if ($seopagebar_options['unter_aut']==0) echo "checked='checked'"; ?> /> <?php _e('Nein', 'seopagebar'); ?>
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Untertitel (Hauptseiten)', 'seopagebar'); ?></th>
						<td>
							<input type="text" name="unter" value="<?php echo stripslashes($seopagebar_options['unter']); ?>" size="50" />					
						</td> 
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('HTML-Tag um den Untertitel', 'seopagebar'); ?></th>
						<td>
							<select name="unter_tag" size="1">
								<option value="h1"<?php if($seopagebar_options['unter_tag']=="h1") echo " selected='selected'"; ?>>h1</option>
								<option value="h2"<?php if($seopagebar_options['unter_tag']=="h2") echo " selected='selected'"; ?>>h2</option>
								<option value="h3"<?php if($seopagebar_options['unter_tag']=="h3") echo " selected='selected'"; ?>>h3</option>
								<option value="h4"<?php if($seopagebar_options['unter_tag']=="h4") echo " selected='selected'"; ?>>h4</option>
								<option value="h5"<?php if($seopagebar_options['unter_tag']=="h5") echo " selected='selected'"; ?>>h5</option>
								<option value="h6"<?php if($seopagebar_options['unter_tag']=="h6") echo " selected='selected'"; ?>>h6</option>
								<option value="p"<?php if($seopagebar_options['unter_tag']=="p") echo " selected='selected'"; ?>>p</option>						
							</select>
							class="<input type="text" name="unter_class" value="<?php echo stripslashes($seopagebar_options['unter_class']); ?>" size="20" />"				
						</td> 
					</tr>
					<tr>
						<td colspan="2" style="border-top: 1px solid #C0C0C0;"></td>
					</tr>
					<tr valign="top"> 
						<th style="width: 250px"><?php _e('Oder ganze Zeile eingeben', 'seopagebar'); ?></th>
						<td>
							<input type="text" name="unter_full" value="<?php echo stripslashes($seopagebar_options['unter_full']); ?>" size="50" /><br />
							<small><?php _e('wenn ausgef&uuml;llt, wird der Rest ignoriert', 'seopagebar'); ?></small>										
						</td> 
					</tr>
				</table>
			</fieldset>
			<br />
			<div align="left">
				<input type="submit" name="do" class="button" value="<?php _e('Einstellungen speichern', 'seopagebar'); ?>" />&nbsp;&nbsp;<input type="submit" name="do" class="button" value="<?php _e('Standardeinstellungen wiederherstellen', 'seopagebar'); ?>" />
			</div>
		</div>
		</form> 
		<div style="margin: 20px 10px 10px 10px; border-top: 1px solid black; width: 70%;"> </div>
		<!-- Seopagebar deinstallieren -->
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
		<div class="wrap"> 
			<h2><?php _e('SEO Pagebar deinstallieren', 'seopagebar'); ?></h2>
			<p style="text-align: left;">
				<?php _e('Falls Du Deine SEO-Pagebar nicht mehr nutzen m&ouml;chtest, kannst Du mit dieser Option die SEO-Pagebar aus der Datenbank entfernen.', 'seopagebar'); ?>
			</p>
			<p style="text-align: left; color: red">
				<strong><?php _e('Warnung:', 'seopagebar'); ?></strong><br />
				<?php _e('Vor jeder &Auml;nderung an der Datenbank sollte man ein Datenbank-Backup anfertigen. Sollte bei der L&ouml;schung der SEO-Pagebar ein Datenbank-Fehler auftreten, was man leider nicht vollst&auml;ndig ausschlie&szlig;en kann, so kann dieser nachtr&auml;glich nicht r&uuml;ckg&auml;ngig gemacht werden!', 'seopagebar'); ?>
			</p>
			<p>
				<input type="checkbox" name="uninstall_seopagebar" value="1" />&nbsp;<?php _e('L&ouml;schen best&auml;tigen', 'seopagebar'); ?><br /><br />
				<input type="submit" name="do" value="<?php _e('SEO Pagebar deinstallieren', 'seopagebar'); ?>" class="button" onclick="return confirm('<?php _e('Soll die SEO Pagebar wirklich deinstalliert werden? Diese Aktion kann nicht r&uuml;ckg&auml;ngig gemacht werden.', 'seopagebar'); ?>')" />
			</p>
		</div> 
		</form>

<?php } ?>